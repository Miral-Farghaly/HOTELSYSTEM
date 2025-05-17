// src/DataStructures.h
#ifndef DATA_STRUCTURES_H
#define DATA_STRUCTURES_H

#include <string>
#include <vector>
#include <nlohmann/json.hpp>

using json = nlohmann::json;

// --- Room Structure (for receiving data) ---
// Fields should match the JSON structure returned by the Laravel API
struct Room {
    int id = 0;
    std::string name = "";
    std::string type = "";
    double price = 0.0;
    std::string bedSize = ""; // Matches bedSize in JSON
    std::string view = "";
    int capacity = 0;
    std::string description = "";
    std::vector<std::string> amenities;
    std::string image = ""; // Assuming URL string
    bool available = true; // Assuming API provides availability
    // Add created_at, updated_at std::string fields if needed
};
// Macro to automatically handle JSON serialization/deserialization for Room
// Ensure the C++ member names match the JSON keys expected/returned by the API
NLOHMANN_DEFINE_TYPE_NON_INTRUSIVE(Room, id, name, type, price, bedSize, view, capacity, description, amenities, image, available);


// --- RoomData Structure (for sending data - POST/PUT) ---
// Contains fields typically required/allowed when creating or updating a room
struct RoomData {
    std::string name = "";
    std::string type = "";
    double price = 0.0;
    std::string bed_size = ""; // Using snake_case assuming API expects this on create/update
    std::string view = "";
    int capacity = 0;
    std::string description = "";
    std::vector<std::string> amenities;
    std::string image = ""; // Optional, might be empty
    bool available = true; // Default availability? Adjust as needed.
};
// Macro for RoomData. Adjust keys (e.g., bed_size) if API expects camelCase (bedSize)
NLOHMANN_DEFINE_TYPE_NON_INTRUSIVE(RoomData, name, type, price, bed_size, view, capacity, description, amenities, image, available);


// --- User Structure ---
struct User {
    int id = 0;
    std::string username = ""; // Or 'name' if API uses that
    std::string email = "";
    std::string phone = "";
    int age = 0;
    std::string role = "";
    // Typically, password is not stored or received in client-side User struct
};
NLOHMANN_DEFINE_TYPE_NON_INTRUSIVE(User, id, username, email, phone, age, role);

// --- Booking Structure (for receiving data) ---
struct Booking {
    int id = 0;
    int userId = 0;     // Matches userId in JSON
    int roomId = 0;     // Matches roomId in JSON
    std::string checkIn = "";  // Matches checkIn in JSON
    std::string checkOut = ""; // Matches checkOut in JSON
    int guests = 0;
    std::string status = "";
    std::string package = "";
    bool housekeeping = false;
    std::string housekeepingTime = ""; // Matches housekeepingTime in JSON
    bool parking = false;
    double totalPrice = 0.0; // Matches totalPrice in JSON
    // Add created_at, updated_at std::string fields if needed
};
// Macro for Booking struct
NLOHMANN_DEFINE_TYPE_NON_INTRUSIVE(Booking, id, userId, roomId, checkIn, checkOut, guests, status, package, housekeeping, housekeepingTime, parking, totalPrice);


// --- BookingData Structure (for sending data - POST) ---
// Fields needed to create a new booking request
struct BookingData {
    int room_id; // Match JSON key expected by API
    std::string check_in;
    std::string check_out;
    int guests;
    std::string package;
    bool housekeeping;
    std::string housekeeping_time; // Optional if housekeeping is false
    bool parking;
};
// Macro for BookingData struct
NLOHMANN_DEFINE_TYPE_NON_INTRUSIVE(BookingData, room_id, check_in, check_out, guests, package, housekeeping, housekeeping_time, parking);


#endif // DATA_STRUCTURES_H