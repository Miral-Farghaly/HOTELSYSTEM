// src/ApiClient_Rooms.cpp
#include "ApiClient.h"
#include "DataStructures.h"
#include <nlohmann/json.hpp>
#include <vector>
#include <optional>
#include <iostream>

using json = nlohmann::json;

// --- Rooms Implementation ---

std::vector<Room> ApiClient::getRooms() {
    std::cout << "[API Request] GET /rooms" << std::endl;
    std::optional<json> response_json_opt = performRequest("GET", "/rooms", 200, false);

    if (!response_json_opt) return {};

    json response_json = response_json_opt.value();
    if (response_json.contains("data") && response_json["data"].is_array()) {
        try {
            return response_json["data"].get<std::vector<Room>>();
        } catch (json::exception& e) {
             std::cerr << "[JSON Error] Failed to convert room list data: " << e.what() << std::endl;
             return {};
        }
    } else {
        std::cerr << "[API Error] Expected 'data' array in /rooms response." << std::endl;
        return {};
    }
}

std::optional<Room> ApiClient::getRoomById(int id) {
    std::string path = "/rooms/" + std::to_string(id);
    std::cout << "[API Request] GET " << path << std::endl;
    std::optional<json> response_json_opt = performRequest("GET", path, 200, false);

    if (!response_json_opt) return std::nullopt;

    json response_json = response_json_opt.value();
    if (response_json.contains("data") && response_json["data"].is_object()) {
         try {
            return response_json["data"].get<Room>();
        } catch (json::exception& e) {
             std::cerr << "[JSON Error] Failed to convert room data for ID " << id << ": " << e.what() << std::endl;
             return std::nullopt;
        }
    } else {
        std::cerr << "[API Error] Expected 'data' object in " << path << " response." << std::endl;
        return std::nullopt;
    }
}

// src/ApiClient_Rooms.cpp
#include "ApiClient.h"
#include "DataStructures.h"
#include <nlohmann/json.hpp>
#include <vector>
#include <optional>
#include <iostream>
#include <string> // Needed for std::to_string

using json = nlohmann::json;

// --- Existing Rooms Implementation (GET methods) ---

std::vector<Room> ApiClient::getRooms() {
    std::cout << "[API Request] GET /rooms" << std::endl;
    std::optional<json> response_json_opt = performRequest("GET", "/rooms", 200, false);

    if (!response_json_opt) return {};

    json response_json = response_json_opt.value();
    if (response_json.contains("data") && response_json["data"].is_array()) {
        try {
            return response_json["data"].get<std::vector<Room>>();
        } catch (json::exception& e) {
             std::cerr << "[JSON Error] Failed to convert room list data: " << e.what() << std::endl;
             return {};
        }
    } else {
        std::cerr << "[API Error] Expected 'data' array in /rooms response." << std::endl;
        return {};
    }
}

std::optional<Room> ApiClient::getRoomById(int id) {
    std::string path = "/rooms/" + std::to_string(id);
    std::cout << "[API Request] GET " << path << std::endl;
    std::optional<json> response_json_opt = performRequest("GET", path, 200, false);

    if (!response_json_opt) return std::nullopt;

    json response_json = response_json_opt.value();
    if (response_json.contains("data") && response_json["data"].is_object()) {
         try {
            return response_json["data"].get<Room>();
        } catch (json::exception& e) {
             std::cerr << "[JSON Error] Failed to convert room data for ID " << id << ": " << e.what() << std::endl;
             return std::nullopt;
        }
    } else {
        std::cerr << "[API Error] Expected 'data' object in " << path << " response." << std::endl;
        return std::nullopt;
    }
}


// --- NEW Rooms Implementation (POST, PUT, DELETE) ---

std::optional<Room> ApiClient::createRoom(const RoomData& roomData) {
    if (!isAuthenticated()) {
        std::cerr << "[Room Error] Authentication required to create a room." << std::endl;
        return std::nullopt;
    }
    // Add role/permission check here if client has that info, otherwise rely on backend
    std::cout << "[API Request] POST /rooms" << std::endl;

    json payload = roomData; // Convert RoomData struct to JSON

    std::optional<json> response_json_opt = performRequest("POST", "/rooms", 201, true, payload);

    if (!response_json_opt) {
        return std::nullopt; // Error handled/logged in performRequest
    }

    json response_json = response_json_opt.value();

    // Expect the created room object wrapped in 'data'
    if (response_json.contains("data") && response_json["data"].is_object()) {
        try {
            // Parse the response back into a full Room struct (which includes the new ID)
            return response_json["data"].get<Room>();
        } catch (json::exception& e) {
            std::cerr << "[JSON Error] Failed to parse created room response: " << e.what() << std::endl;
            return std::nullopt;
        }
    } else {
        std::cerr << "[API Error] Expected 'data' object in room creation response." << std::endl;
        return std::nullopt;
    }
}


bool ApiClient::updateRoom(int id, const RoomData& roomData) {
    if (!isAuthenticated()) {
        std::cerr << "[Room Error] Authentication required to update a room." << std::endl;
        return false;
    }
    // Add role/permission check here if possible
    std::string path = "/rooms/" + std::to_string(id);
    std::cout << "[API Request] PUT " << path << std::endl;

    json payload = roomData; // Convert RoomData struct to JSON

    // Expect 200 OK on successful update
    std::optional<json> response_json_opt = performRequest("PUT", path, 200, true, payload);

    // Check if the request was successful (returned a value, implying status 200 OK)
    if (response_json_opt.has_value()) {
        std::cout << "[Room] Update successful for room ID: " << id << std::endl;
        // Optionally, parse response_json_opt.value() if the API returns the updated room data
        return true;
    } else {
        std::cerr << "[Room Error] Update failed for room ID: " << id << std::endl;
        return false;
    }
}


bool ApiClient::deleteRoom(int id) {
    if (!isAuthenticated()) {
        std::cerr << "[Room Error] Authentication required to delete a room." << std::endl;
        return false;
    }
    // Add role/permission check here if possible
    std::string path = "/rooms/" + std::to_string(id);
    std::cout << "[API Request] DELETE " << path << std::endl;

    // Expect 204 No Content or 200 OK for successful deletion
    // Let's check for 204 first as it's common for DELETE.
    std::optional<json> response_json_opt = performRequest("DELETE", path, 204, true);

    if (!response_json_opt) {
         // If 204 failed, maybe the backend returns 200 OK?
         std::cout << "[Room Info] Delete didn't return 204, checking for 200..." << std::endl;
         response_json_opt = performRequest("DELETE", path, 200, true);
    }

    // Check if either attempt resulted in success (returned a value)
    if (response_json_opt.has_value()) {
         std::cout << "[Room] Successfully deleted room ID: " << id << std::endl;
         return true;
    } else {
         std::cerr << "[Room Error] Failed to delete room ID: " << id << std::endl;
         return false;
    }
}