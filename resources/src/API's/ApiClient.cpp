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
    // Basic validation could be added here if needed
    if (base_url.empty()) {
        std::cerr << "[Config Error] API base URL cannot be empty!" << std::endl;
        // Consider throwing an exception or setting a default invalid state
    }
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
            // This situation should ideally be prevented by checks in the public methods
            std::cerr << "[Header Warning] Auth required but client is not authenticated." << std::endl;
            // Depending on application logic, you might want to throw here:
            // throw std::runtime_error("Authentication required but token is missing.");
        }
    }
    return headers;
}

// Central place to handle response status checks and JSON parsing
std::optional<json> ApiClient::handleResponse(const cpr::Response& response, int expectedStatus) {
     // Log status code and potentially truncated body for debugging
     std::cout << "[API Response] Status: " << response.status_code;
     if (response.text.length() < 500) { // Limit log size
         std::cout << ", Body: " << response.text << std::endl;
     } else {
          std::cout << ", Body: (Truncated - " << response.text.length() << " bytes)" << std::endl;
     }

    // Check for CPR library-level errors (network issues, etc.)
    if (response.error) {
        std::cerr << "[API Error] CPR Error (" << static_cast<int>(response.error.code) << "): " << response.error.message << std::endl;
        return std::nullopt;
    }

    // Check if the HTTP status code matches the expected one
    if (response.status_code != expectedStatus) {
        std::cerr << "[API Error] Expected status " << expectedStatus << " but received " << response.status_code << "." << std::endl;
        // Attempt to parse the body as JSON for more detailed error messages from the API
        try {
            if (!response.text.empty()) {
                json error_json = json::parse(response.text);
                // Look for common Laravel error structures
                if (error_json.contains("message")) {
                    std::cerr << "[API Error Message] " << error_json["message"].get<std::string>() << std::endl;
                }
                 if (error_json.contains("errors")) { // Laravel validation errors
                     std::cerr << "[API Validation Errors] " << error_json["errors"].dump(2) << std::endl;
                 } else if (!error_json.contains("message")){
                     std::cerr << "[API Error Body] " << error_json.dump(2) << std::endl; // Pretty print if unknown structure
                 }
            } else {
                 std::cerr << "[API Error Body] (Empty)" << std::endl;
            }
        } catch (json::parse_error&) {
            // If the error response isn't JSON, print the raw text
            std::cerr << "[API Error Body] " << response.text << std::endl;
        }
        return std::nullopt; // Indicate failure
    }

    // Handle successful responses (matching expected status)

    // Case 1: Successful response with no content expected (e.g., 204) or empty body
    if (expectedStatus == 204 || response.text.empty()) {
         if (response.text.empty() && expectedStatus != 204) {
             // Log if body is unexpectedly empty for statuses other than 204
             std::cout << "[API Info] Received empty response body for status " << response.status_code << "." << std::endl;
         }
        // Return an empty JSON object to signify success without parseable data
        return json({});
    }

    // Case 2: Successful response with content, try parsing JSON
    try {
        return json::parse(response.text);
    } catch (json::parse_error& e) {
        std::cerr << "[JSON Error] Failed to parse successful response: " << e.what() << std::endl;
        std::cerr << "[JSON Error] Raw Response Text: " << response.text << std::endl;
        return std::nullopt; // Indicate failure due to parsing error
    }
}

// Central request function using CPR
std::optional<json> ApiClient::performRequest(
    const std::string& method,
    const std::string& relative_path,
    int expectedStatus,
    bool requiresAuth,
    const std::optional<json>& payload)
{
    // Construct the full URL
    cpr::Url url = cpr::Url{base_url_ + relative_path};
    // Prepare headers, including auth if needed
    cpr::Header headers = prepareHeaders(requiresAuth);
    // Declare response variable
    cpr::Response response;

    // Prepare body if payload is provided
    cpr::Body body = payload.has_value() ? cpr::Body{payload.value().dump()} : cpr::Body{""};

    // --- Execute HTTP Request based on method ---
    try {
        if (method == "GET") {
            response = cpr::Get(url, headers);
        } else if (method == "POST") {
             if (!payload.has_value()) { // POST typically requires a body
                 std::cerr << "[Request Error] POST request to " << relative_path << " called without a payload." << std::endl;
                 return std::nullopt;
             }
            response = cpr::Post(url, headers, body);
        } else if (method == "PUT") {
             if (!payload.has_value()) { // PUT typically requires a body
                 std::cerr << "[Request Error] PUT request to " << relative_path << " called without a payload." << std::endl;
                 return std::nullopt;
             }
            response = cpr::Put(url, headers, body);
        } else if (method == "DELETE") {
            response = cpr::Delete(url, headers); // DELETE may or may not have a body depending on API
        } else {
            std::cerr << "[Request Error] Unsupported HTTP method provided: " << method << std::endl;
            return std::nullopt;
        }
    } catch (const std::exception& e) {
         // Catch potential exceptions during the cpr:: call itself (less common)
         std::cerr << "[Request Error] Exception during HTTP request (" << method << " " << relative_path << "): " << e.what() << std::endl;
         return std::nullopt;
    }

    // Handle the response (checks status, parses JSON)
    return handleResponse(response, expectedStatus);
}