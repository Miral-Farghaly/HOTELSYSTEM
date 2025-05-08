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

// TODO: Implement POST, PUT, DELETE for rooms if needed by the client
// Example (POST - requires Room struct to be adapted or a new RoomData struct):
/*
bool ApiClient::createRoom(const Room& roomData) {
    if (!isAuthenticated()) return false; // Add role check if needed
    json payload = roomData; // Might need adjustment
    std::cout << "[API Request] POST /rooms" << std::endl;
    auto response = performRequest("POST", "/rooms", 201, true, payload);
    return response.has_value();
}
*/