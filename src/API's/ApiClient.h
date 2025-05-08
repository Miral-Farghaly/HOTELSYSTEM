// src/ApiClient.h
#ifndef API_CLIENT_H
#define API_CLIENT_H

#include <string>
#include <vector>
#include <optional>
#include <nlohmann/json.hpp>
#include "DataStructures.h"

namespace cpr {
    class Response;
    class Header;
}
using json = nlohmann::json;


class ApiClient {
private:
    std::string base_url_;
    std::string auth_token_;
// --- Rooms ---
std::vector<Room> getRooms();                  // GET /rooms
std::optional<Room> getRoomById(int id);       // GET /rooms/{id}
// *** UPDATED DECLARATIONS ***
std::optional<Room> createRoom(const RoomData& roomData); // POST /rooms (Requires Auth)
bool updateRoom(int id, const RoomData& roomData);      // PUT /rooms/{id} (Requires Auth)
bool deleteRoom(int id);    
    cpr::Header prepareHeaders(bool requiresAuth = false);
    std::optional<json> handleResponse(const cpr::Response& response, int expectedStatus = 200);
    std::optional<json> performRequest(
        const std::string& method,
        const std::string& relative_path,
        int expectedStatus,
        bool requiresAuth,
        const std::optional<json>& payload = std::nullopt
    );

public:
    ApiClient(const std::string& base_url);

    bool isAuthenticated() const;

    // --- Authentication ---
    bool login(const std::string& email, const std::string& password, const std::string& role = "user");
    bool signup(const std::string& username, const std::string& email, const std::string& password, const std::string& phone, int age);
    bool logout();

    // --- Rooms ---
    std::vector<Room> getRooms();                  // GET /rooms
    std::optional<Room> getRoomById(int id);       // GET /rooms/{id}
    // *** ADDED DECLARATIONS ***
    std::optional<Room> createRoom(const Room& roomData); // POST /rooms (Requires Auth) - Note: Pass relevant data
    bool updateRoom(int id, const Room& roomData); // PUT /rooms/{id} (Requires Auth) - Note: Pass relevant data
    bool deleteRoom(int id);                       // DELETE /rooms/{id} (Requires Auth)
    // *************************

    // --- Bookings ---
    std::optional<Booking> createBooking(const BookingData& bookingData);
    std::vector<Booking> getBookings();
    std::optional<Booking> getBookingById(int id);
    bool deleteBooking(int id);

    // --- User Profile ---
    std::optional<User> getUserProfile(int id);
    bool updateUserProfile(int id, const User& userData);

};

#endif // API_CLIENT_H