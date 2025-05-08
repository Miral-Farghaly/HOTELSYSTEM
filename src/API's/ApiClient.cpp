// src/ApiClient.cpp
#include "ApiClient.h"
#include <cpr/cpr.h>
#include <nlohmann/json.hpp>
#include <iostream>
#include <optional>
#include <stdexcept> // For exceptions

using json = nlohmann::json;

// --- Constructor ---
ApiClient::ApiClient(const std::string& base_url) : base_url_(base_url), auth_token_("") {
    std::cout << "ApiClient initialized with base URL: " << base_url_ << std::endl;
}

// --- Public Authentication Check ---
bool ApiClient::isAuthenticated() const {
    return !auth_token_.empty();
}


// --- Private Helpers Implementation ---

cpr::Header ApiClient::prepareHeaders(bool requiresAuth) {
    cpr::Header headers = {
        {"Content-Type", "application/json"},
        {"Accept", "application/json"}
    };
    if (requiresAuth) {
        if (isAuthenticated()) {
            headers["Authorization"] = "Bearer " + auth_token_;
        } else {
            std::cerr << "[Header Warning] Auth required but client is not authenticated." << std::endl;
            throw std::runtime_error("Authentication required but not available.");
        }
    }
    return headers;
}

std::optional<json> ApiClient::handleResponse(const cpr::Response& response, int expectedStatus) {
     std::cout << "[API Response] Status: " << response.status_code;
     if (response.text.length() < 500) {
         std::cout << ", Body: " << response.text << std::endl;
     } else {
          std::cout << ", Body: (Truncated - " << response.text.length() << " bytes)" << std::endl;
     }


    if (response.error) {
        std::cerr << "[API Error] CPR Error: " << response.error.message << std::endl;
        return std::nullopt;
    }

    if (response.status_code != expectedStatus) {
        std::cerr << "[API Error] Expected status " << expectedStatus << " but received " << response.status_code << "." << std::endl;
        try {
            json error_json = json::parse(response.text);
            std::cerr << "[API Error Body] " << error_json.dump(2) << std::endl;
        } catch (json::parse_error&) {
            std::cerr << "[API Error Body] " << response.text << std::endl;
        }
        return std::nullopt;
    }

    if (expectedStatus == 204 || response.text.empty()) {
         if (response.text.empty() && expectedStatus != 204) {
             std::cout << "[API Warning] Received empty response body for status " << response.status_code << "." << std::endl;
         }
        return json({}); // Indicate success with no content or empty content
    }

    try {
        return json::parse(response.text);
    } catch (json::parse_error& e) {
        std::cerr << "[JSON Error] Failed to parse response: " << e.what() << std::endl;
        std::cerr << "[JSON Error] Raw Response: " << response.text << std::endl;
        return std::nullopt;
    }
}

// Central request function
std::optional<json> ApiClient::performRequest(
    const std::string& method,
    const std::string& relative_path,
    int expectedStatus,
    bool requiresAuth,
    const std::optional<json>& payload)
{
    cpr::Url url = cpr::Url{base_url_ + relative_path};
    cpr::Header headers = prepareHeaders(requiresAuth);
    cpr::Response response;

    // Add payload if provided (usually for POST/PUT)
    cpr::Body body = payload.has_value() ? cpr::Body{payload.value().dump()} : cpr::Body{""};

    // Execute the correct CPR function based on the method
    if (method == "GET") {
        response = cpr::Get(url, headers);
    } else if (method == "POST") {
        if (!payload.has_value()) {
             std::cerr << "[Request Error] POST request to " << relative_path << " requires a payload." << std::endl;
             return std::nullopt; // Or handle differently
        }
        response = cpr::Post(url, headers, body);
    } else if (method == "PUT") {
         if (!payload.has_value()) {
             std::cerr << "[Request Error] PUT request to " << relative_path << " requires a payload." << std::endl;
             return std::nullopt; // Or handle differently
        }
        response = cpr::Put(url, headers, body);
    } else if (method == "DELETE") {
        response = cpr::Delete(url, headers);
    } else {
        std::cerr << "[Request Error] Unsupported HTTP method: " << method << std::endl;
        return std::nullopt;
    }

    return handleResponse(response, expectedStatus);
}