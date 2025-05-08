// src/ApiClient_Bookings.cpp
#include "ApiClient.h"
#include "DataStructures.h"
#include <nlohmann/json.hpp>
#include <vector>
#include <optional>
#include <iostream>
#include <string> // Needed for std::to_string

using json = nlohmann::json;

// --- Bookings Implementation ---

std::optional<Booking> ApiClient::createBooking(const BookingData& bookingData) {
    if (!isAuthenticated()) {
        std::cerr << "[Booking Error] Authentication required to create a booking." << std::endl;
        return std::nullopt;
    }
    std::cout << "[API Request] POST /bookings" << std::endl;
    json payload = bookingData; // Convert BookingData struct to JSON

    // Expect HTTP 201 Created for successful booking creation
    std::optional<json> response_json_opt = performRequest("POST", "/bookings", 201, true, payload);

    if (!response_json_opt) {
        return std::nullopt; // Error logged by performRequest/handleResponse
    }

    json response_json = response_json_opt.value();
    // Expect the created booking object wrapped in 'data'
    if (response_json.contains("data") && response_json["data"].is_object()) {
         try {
            // Parse the response back into a full Booking struct
            return response_json["data"].get<Booking>();
        } catch (json::exception& e) {
             std::cerr << "[JSON Error] Failed to parse created booking response: " << e.what() << std::endl;
             return std::nullopt;
        }
    } else {
         std::cerr << "[API Error] Expected 'data' object in booking creation response." << std::endl;
         return std::nullopt;
    }
}

std::vector<Booking> ApiClient::getBookings() {
     if (!isAuthenticated()) {
        std::cerr << "[Booking Error] Authentication required to view bookings." << std::endl;
        return {};
    }
     std::cout << "[API Request] GET /bookings" << std::endl;
     // Backend should filter bookings based on authenticated user/role
     std::optional<json> response_json_opt = performRequest("GET", "/bookings", 200, true);

    if (!response_json_opt) return {}; // Return empty vector on error

    json response_json = response_json_opt.value();
    // Expect Laravel collection resource format: { "data": [ ... ] }
    if (response_json.contains("data") && response_json["data"].is_array()) {
        try {
            return response_json["data"].get<std::vector<Booking>>();
        } catch (json::exception& e) {
             std::cerr << "[JSON Error] Failed to convert booking list data: " << e.what() << std::endl;
             return {};
        }
    } else {
        std::cerr << "[API Error] Expected 'data' array in /bookings response." << std::endl;
        return {};
    }
}

std::optional<Booking> ApiClient::getBookingById(int id) {
    if (!isAuthenticated()) {
        std::cerr << "[Booking Error] Authentication required to view a specific booking." << std::endl;
        return std::nullopt;
    }
    std::string path = "/bookings/" + std::to_string(id);
    std::cout << "[API Request] GET " << path << std::endl;
    // Backend must enforce authorization (can user view this specific booking?)
    std::optional<json> response_json_opt = performRequest("GET", path, 200, true);

    if (!response_json_opt) return std::nullopt;

    json response_json = response_json_opt.value();
    // Expect Laravel single resource format: { "data": { ... } }
    if (response_json.contains("data") && response_json["data"].is_object()) {
         try {
            return response_json["data"].get<Booking>();
        } catch (json::exception& e) {
             std::cerr << "[JSON Error] Failed to convert booking data for ID " << id << ": " << e.what() << std::endl;
             return std::nullopt;
        }
    } else {
        std::cerr << "[API Error] Expected 'data' object in " << path << " response." << std::endl;
        return std::nullopt;
    }
}

bool ApiClient::deleteBooking(int id) {
    if (!isAuthenticated()) {
        std::cerr << "[Booking Error] Authentication required to delete a booking." << std::endl;
        return false;
    }
    std::string path = "/bookings/" + std::to_string(id);
    std::cout << "[API Request] DELETE " << path << std::endl;
    // Backend must enforce authorization

    // Expect 204 No Content or maybe 200 OK for successful deletion
    std::optional<json> response_json_opt = performRequest("DELETE", path, 204, true); // Try 204 first

     if (!response_json_opt) {
          // If 204 failed, maybe the backend returns 200 OK?
          std::cout << "[Booking Info] Delete didn't return 204, checking for 200..." << std::endl;
          response_json_opt = performRequest("DELETE", path, 200, true);
     }

    // Check if either attempt resulted in success (returned a value)
    if (response_json_opt.has_value()) {
         std::cout << "[Booking] Successfully deleted booking ID: " << id << std::endl;
         return true;
    } else {
         // Error message was already logged by performRequest/handleResponse
         std::cerr << "[Booking Error] Failed to delete booking ID: " << id << std::endl;
         return false;
    }
}