// src/ApiClient.h
#ifndef API_CLIENT_H
#define API_CLIENT_H

#include <string>
#include <vector>
#include <optional>
#include <nlohmann/json.hpp> // Include json header
#include "DataStructures.h"  // Include our structs

// Forward declare cpr::Response and cpr::Header
namespace cpr {
    class Response;
    class Header; // Forward declare Header as well
}
using json = nlohmann::json;


class ApiClient {
private:
    std::string base_url_;
    std::string auth_token_;

    // --- Private Helpers ---
    cpr::Header prepareHeaders(bool requiresAuth = false);
    std::optional<json> handleResponse(const cpr::Response& response, int expectedStatus = 200);

    // Central method to perform HTTP requests
    std::optional<json> performRequest(
        const std::string& method,           // e.g., "GET", "POST"
        const std::string& relative_path,    // e.g., "/login", "/rooms/5"
        int expectedStatus,                 // Expected HTTP status for success
        bool requiresAuth,                  // Does this request need the auth token?
        const std::optional<json>& payload = std::nullopt // Optional JSON body for POST/PUT
    );

public:
    ApiClient(const std::string& base_url);

    bool isAuthenticated() const;

    // --- Authentication (Declarations only) ---
    bool login(const std::string& email, const std::string& password, const std::string& role = "user");
    bool signup(const std::string& username, const std::string& email, const std::string& password, const std::string& phone, int age);
    bool logout();

    // --- Rooms (Declarations only) ---
    std::vector<Room> getRooms();
    std::optional<Room> getRoomById(int id);
    // Add declarations for POST, PUT, DELETE rooms if needed

    // --- Bookings (Declarations only) ---
    std::optional<Booking> createBooking(const BookingData& bookingData);
    std::vector<Booking> getBookings();
    std::optional<Booking> getBookingById(int id);
    bool deleteBooking(int id);

    // --- User Profile (Declarations only) ---
    std::optional<User> getUserProfile(int id);
    bool updateUserProfile(int id, const User& userData); // Note: Pass necessary fields, not necessarily whole User struct

};

#endif // API_CLIENT_H