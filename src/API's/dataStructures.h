// src/DataStructures.h
#ifndef DATA_STRUCTURES_H
#define DATA_STRUCTURES_H

#include <string>
#include <vector>
#include <nlohmann/json.hpp>

using json = nlohmann::json;

// --- Room Structure (for receiving data) ---
struct Room {
    int id = 0;
    std::string name;
    std::string type;
    double price = 0.0;
    std::string bedSize; // bed_size in JSON
    std::string view;
    int capacity = 0;
    std::string description;
    std::vector<std::string> amenities;
    std::string image; // Assuming URL
    // Add bool available if needed
    // Add created_at, updated_at if the API includes them and you need them
};
NLOHMANN_DEFINE_TYPE_NON_INTRUSIVE(Room, id, name, type, price, bedSize, view, capacity, description, amenities, image);


// --- RoomData Structure (for sending data - POST/PUT) ---
// Contains fields typically required/allowed when creating or updating
struct RoomData {
    std::string name;
    std::string type;
    double price = 0.0;
    std::string bed_size; // Match backend expected snake_case?
    std::string view;
    int capacity = 0;
    std::string description;
    std::vector<std::string> amenities;
    std::string image; // Optional, maybe allow null/empty?
    bool available = true; // Default availability? Adjust as needed.
};
// Note: Using snake_case here assuming Laravel backend might expect it in request bodies
// Adjust if your backend uses camelCase for requests.
NLOHMANN_DEFINE_TYPE_NON_INTRUSIVE(RoomData, name, type, price, bed_size, view, capacity, description, amenities, image, available);


// --- User Structure ---
struct User {
    int id = 0;
    std::string username;
    std::string email;
    std::string phone;
    int age = 0;
    std::string role;
};
NLOHMANN_DEFINE_TYPE_NON_INTRUSIVE(User, id, username, email, phone, age, role);

// --- Booking Structure (for receiving data) ---
struct Booking {
    int id = 0;
    int userId = 0;
    int roomId = 0;
    std::string checkIn;
    std::string checkOut;
    int guests = 0;
    std::string status;
    std::string package;
    bool housekeeping = false;
    std::string housekeepingTime;
    bool parking = false;
    double totalPrice = 0.0;
};
NLOHMANN_DEFINE_TYPE_NON_INTRUSIVE(Booking, id, userId, roomId, checkIn, checkOut, guests, status, package, housekeeping, housekeepingTime, parking, totalPrice);

// --- BookingData Structure (for sending data - POST) ---
struct BookingData {
    int room_id;
    std::string check_in;
    std::string check_out;
    int guests;
    std::string package;
    bool housekeeping;
    std::string housekeeping_time; // Optional if housekeeping is false
    bool parking;
};
NLOHMANN_DEFINE_TYPE_NON_INTRUSIVE(BookingData, room_id, check_in, check_out, guests, package, housekeeping, housekeeping_time, parking);


#endif // DATA_STRUCTURES_H