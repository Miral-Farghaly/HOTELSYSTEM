// src/ApiClient_User.cpp
#include "ApiClient.h"
#include "DataStructures.h"
#include <nlohmann/json.hpp>
#include <vector>
#include <optional>
#include <iostream>
#include <string> // Needed for std::to_string

using json = nlohmann::json;

// --- User Profile Implementation ---

std::optional<User> ApiClient::getUserProfile(int id) {
     if (!isAuthenticated()) {
        std::cerr << "[User Error] Authentication required to view user profiles." << std::endl;
        return std::nullopt;
    }
     std::string path = "/user/" + std::to_string(id);
     std::cout << "[API Request] GET " << path << std::endl;
     // Note: Backend must enforce authorization (can current user view profile 'id'?)

     // Expect 200 OK
     std::optional<json> response_json_opt = performRequest("GET", path, 200, true);

    if (!response_json_opt) return std::nullopt; // Error logged already

    json response_json = response_json_opt.value();
    // Expect Laravel single resource format: { "data": { ... } }
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
        std::cerr << "[User Error] Authentication required to update user profiles." << std::endl;
        return false;
    }
     std::string path = "/user/" + std::to_string(id);
     std::cout << "[API Request] PUT " << path << std::endl;
     // Note: Backend must enforce authorization (can current user update profile 'id'?)

    // Construct payload carefully - avoid sending sensitive fields like ID, role, password
    // Ensure the keys match what the backend expects for an update request
    json payload = {
        {"username", userData.username}, // or "name"
        {"email", userData.email},
        {"phone", userData.phone},
        {"age", userData.age}
        // Do NOT include id, role, or password unless API specifically requires them for update
    };

    // Expect 200 OK on successful update
    std::optional<json> response_json_opt = performRequest("PUT", path, 200, true, payload);

    // Check if the request was successful (returned a value, implying status 200 OK)
    if (response_json_opt.has_value()) {
         std::cout << "[User] Profile update successful for ID: " << id << std::endl;
         // The response might contain the updated user data in response_json_opt.value()["data"]
         // You could parse and use it if needed, e.g., update the local loggedInUser object.
         return true;
    } else {
         std::cerr << "[User Error] Profile update failed for ID: " << id << std::endl;
         return false;
    }
}