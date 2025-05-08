// src/ApiClient_User.cpp
#include "ApiClient.h"
#include "DataStructures.h"
#include <nlohmann/json.hpp>
#include <vector>
#include <optional>
#include <iostream>

using json = nlohmann::json;

// --- User Profile Implementation ---

std::optional<User> ApiClient::getUserProfile(int id) {
     if (!isAuthenticated()) {
        std::cerr << "[User Error] Authentication required." << std::endl;
        return std::nullopt;
    }
     // Note: Backend must enforce authorization (can current user view profile 'id'?)
     std::string path = "/user/" + std::to_string(id);
     std::cout << "[API Request] GET " << path << std::endl;

     std::optional<json> response_json_opt = performRequest("GET", path, 200, true);

    if (!response_json_opt) return std::nullopt;

    json response_json = response_json_opt.value();
    if (response_json.contains("data") && response_json["data"].is_object()) {
         try {
            return response_json["data"].get<User>();
        } catch (json::exception& e) {
             std::cerr << "[JSON Error] Failed to convert user profile data for ID " << id << ": " << e.what() << std::endl;
             return std::nullopt;
        }
    } else {
        std::cerr << "[API Error] Expected 'data' object in " << path << " response." << std::endl;
        return std::nullopt;
    }
}

bool ApiClient::updateUserProfile(int id, const User& userData) {
    if (!isAuthenticated()) {
        std::cerr << "[User Error] Authentication required." << std::endl;
        return false;
    }
    // Note: Backend must enforce authorization (can current user update profile 'id'?)
    std::string path = "/user/" + std::to_string(id);
    std::cout << "[API Request] PUT " << path << std::endl;

    // Construct payload carefully - avoid sending sensitive fields like ID, role, password
    json payload = {
        {"username", userData.username}, // or "name"
        {"email", userData.email},
        {"phone", userData.phone},
        {"age", userData.age}
    };

    std::optional<json> response_json_opt = performRequest("PUT", path, 200, true, payload);

    // We just need to know if the request succeeded (status 200)
    if(response_json_opt.has_value()) {
         std::cout << "[User] Profile update successful for ID: " << id << std::endl;
         return true;
    } else {
         std::cerr << "[User Error] Profile update failed for ID: " << id << std::endl;
         return false;
    }
}