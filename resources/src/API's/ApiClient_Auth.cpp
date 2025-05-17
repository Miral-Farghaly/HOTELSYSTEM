// src/ApiClient_Auth.cpp
#include "ApiClient.h"
#include <nlohmann/json.hpp>
#include <iostream>
#include <optional>

using json = nlohmann::json;

// --- Authentication Implementation ---

bool ApiClient::login(const std::string& email, const std::string& password, const std::string& role) {
    json payload = {
        {"email", email},
        {"password", password}
         {"role", role} // Add only if backend requires it
    };
    std::cout << "[API Request] POST /login" << std::endl;

    // Use the central performRequest helper
    std::optional<json> response_json_opt = performRequest("POST", "/login", 200, false, payload);

    if (!response_json_opt) {
        auth_token_.clear();
        return false;
    }

    json response_json = response_json_opt.value();
    if (response_json.contains("token") && response_json["token"].is_string()) {
        auth_token_ = response_json["token"];
        std::cout << "[Auth] Login successful. Token stored." << std::endl;
        return true;
    } else {
        std::cerr << "[Auth Error] Login succeeded (status 200) but token not found in response." << std::endl;
        auth_token_.clear();
        return false;
    }
}

bool ApiClient::signup(const std::string& username, const std::string& email, const std::string& password, const std::string& phone, int age) {
     json payload = {
        {"username", username}, // Or "name"
        {"email", email},
        {"password", password},
        {"password_confirmation", password},
        {"phone", phone},
        {"age", age}
    };
    std::cout << "[API Request] POST /signup" << std::endl;

    std::optional<json> response_json_opt = performRequest("POST", "/signup", 201, false, payload);

     if (!response_json_opt) {
        return false; // Error logged in handleResponse via performRequest
    }

    json response_json = response_json_opt.value();
    if (response_json.contains("token") && response_json["token"].is_string()) {
         auth_token_ = response_json["token"];
         std::cout << "[Auth] Signup successful. User automatically logged in." << std::endl;
    } else {
         std::cout << "[Auth] Signup successful. User created, please login separately." << std::endl;
    }
    return true;
}

bool ApiClient::logout() {
    if (!isAuthenticated()) {
        std::cerr << "[Auth Error] Cannot logout: No user is currently authenticated." << std::endl;
        return true; // Already in desired state
    }
    std::cout << "[API Request] POST /logout" << std::endl;

    // Logout might return 200 or 204. We check for 204 first as it's common.
    // performRequest handles logging if the status is unexpected.
    std::optional<json> response_json_opt = performRequest("POST", "/logout", 204, true); // Try 204 first
     if (!response_json_opt) {
         // If 204 failed, maybe backend returns 200? Let's try again (optional, depends on API)
          std::cout << "[Auth Info] Logout didn't return 204, checking for 200..." << std::endl;
          response_json_opt = performRequest("POST", "/logout", 200, true);
          if (!response_json_opt) {
               std::cerr << "[Auth Warning] Logout request failed on server. Clearing token locally." << std::endl;
          } else {
               std::cout << "[Auth] Logout successful on server (returned 200)." << std::endl;
          }

     } else {
          std::cout << "[Auth] Logout successful on server (returned 204)." << std::endl;
     }


    // Always clear the token locally on logout attempt
    auth_token_.clear();
    std::cout << "[Auth] Local token cleared." << std::endl;
    return true;
}