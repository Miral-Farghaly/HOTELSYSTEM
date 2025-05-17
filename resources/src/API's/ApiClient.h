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
    class Header;
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
    std::optional<User> login(const std::string& email, const std::string& password, const std::string& role = "user");
    bool signup(const std::string& username, const std::string& email, const std::string& password, const std::string& phone, int age);
    bool logout();

    // --- Rooms (Declarations only) ---
    std::vector<Room> getRooms();                  // GET /rooms
    std::optional<Room> getRoomById(int id);       // GET /rooms/{id}
    std::optional<Room> createRoom(const RoomData& roomData); // POST /rooms (Requires Auth)
    bool updateRoom(int id, const RoomData& roomData);      // PUT /rooms/{id} (Requires Auth)
    bool deleteRoom(int id);                                // DELETE /rooms/{id} (Requires Auth)


    // --- Bookings (Declarations only) ---
    std::optional<Booking> createBooking(const BookingData& bookingData);
    std::vector<Booking> getBookings();
    std::optional<Booking> getBookingById(int id);
    bool deleteBooking(int id);

    // --- User Profile (Declarations only) ---
    std::optional<User> getUserProfile(int id);
    // Note: Pass relevant fields, User struct might contain fields not allowed in update (id, role)
    bool updateUserProfile(int id, const User& userData);


};

#endif // API_CLIENT_H