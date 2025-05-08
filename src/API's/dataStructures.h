// src/DataStructures.h
#ifndef DATA_STRUCTURES_H
#define DATA_STRUCTURES_H

#include <string>
#include <vector>
#include <nlohmann/json.hpp> // Include json header

using json = nlohmann::json;

struct Room {
    int id = 0;
    std::string name;
    std::string type;
    double price = 0.0;
    std::string bedSize;
    std::string view;
    int capacity = 0;
    std::string description;
    std::vector<std::string> amenities;
    std::string image; // Assuming URL
    // Add available, created_at, updated_at if needed from API
};
// Helper macro for nlohmann/json to automatically convert to/from JSON
NLOHMANN_DEFINE_TYPE_NON_INTRUSIVE(Room, id, name, type, price, bedSize, view, capacity, description, amenities, image);

struct User {
    int id = 0;
    std::string username; // Or 'name' depending on backend
    std::string email;
    std::string phone;
    int age = 0;
    std::string role;
    // Don't include password here usually
};
NLOHMANN_DEFINE_TYPE_NON_INTRUSIVE(User, id, username, email, phone, age, role);


struct Booking {
    int id = 0;
    int userId = 0; // user_id in JSON
    int roomId = 0; // room_id in JSON
    std::string checkIn; // check_in in JSON
    std::string checkOut; // check_out in JSON
    int guests = 0;
    std::string status;
    std::string package;
    bool housekeeping = false;
    std::string housekeepingTime; // housekeeping_time in JSON
    bool parking = false;
    double totalPrice = 0.0; // total_price in JSON
    // Add created_at, updated_at if needed
};
// Map C++ names to potential snake_case JSON names from Laravel
NLOHMANN_DEFINE_TYPE_NON_INTRUSIVE(Booking, id, userId, roomId, checkIn, checkOut, guests, status, package, housekeeping, housekeepingTime, parking, totalPrice);

// You might need a simpler struct for *creating* a booking
struct BookingData {
    int room_id;
    std::string check_in;
    std::string check_out;
    int guests;
    std::string package;
    bool housekeeping;
    std::string housekeeping_time;
    bool parking;
    // user_id will be inferred from the auth token on the backend
};
NLOHMANN_DEFINE_TYPE_NON_INTRUSIVE(BookingData, room_id, check_in, check_out, guests, package, housekeeping, housekeeping_time, parking);


#endif // DATA_STRUCTURES_H