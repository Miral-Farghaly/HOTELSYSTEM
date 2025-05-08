// src/main.cpp
#include <iostream>
#include <vector>
#include <string>
#include "ApiClient.h"
#include "DataStructures.h"

int main() {
    // Replace with the actual URL of the running Laravel API
    std::string api_base_url = "http://localhost:8000/api";
    ApiClient client(api_base_url);

    std::cout << "--- Hotel Client Application ---" << std::endl;

    // --- Example: Login ---
    std::string email, password;
    std::cout << "Enter email: ";
    std::cin >> email;
    std::cout << "Enter password: ";
    // NOTE: In a real app, don't echo password to console!
    // Use platform-specific methods to hide input.
    std::cin >> password;

    if (client.login(email, password)) {
        std::cout << "\nLogin successful!" << std::endl;

        // --- Example: Get Rooms (after login) ---
        std::cout << "\nFetching rooms..." << std::endl;
        std::vector<Room> rooms = client.getRooms();
        if (!rooms.empty()) {
            std::cout << "Available Rooms:" << std::endl;
            for (const auto& room : rooms) {
                std::cout << " - ID: " << room.id << ", Name: " << room.name
                          << ", Type: " << room.type << ", Price: $" << room.price << std::endl;
            }

            // --- Example: Create Booking ---
            if (!rooms.empty()) {
                std::cout << "\nAttempting to book room ID: " << rooms[0].id << std::endl;
                BookingData newBooking;
                newBooking.room_id = rooms[0].id;
                newBooking.check_in = "2024-09-15"; // Use actual dates
                newBooking.check_out = "2024-09-18";
                newBooking.guests = 2;
                newBooking.package = "Gold";
                newBooking.housekeeping = true;
                newBooking.housekeeping_time = "10:00";
                newBooking.parking = false;

                auto createdBookingOpt = client.createBooking(newBooking);
                if (createdBookingOpt) {
                    std::cout << "Booking successful! Booking ID: " << createdBookingOpt.value().id << std::endl;
                    // Use createdBookingOpt.value() fields
                } else {
                    std::cerr << "Booking failed." << std::endl;
                }
            }

        } else {
            std::cerr << "Failed to fetch rooms or no rooms available." << std::endl;
        }


        // --- Example: Logout ---
        std::cout << "\nLogging out..." << std::endl;
        if (client.logout()) {
            std::cout << "Logout successful." << std::endl;
        } else {
             std::cout << "Logout completed (token cleared locally)." << std::endl;
        }

    } else {
        std::cerr << "Login failed. Please check credentials." << std::endl;
    }


     // --- Example: Trying an authenticated route after logout ---
     std::cout << "\nAttempting to get rooms after logout (should fail authentication if API requires it)..." << std::endl;
     // Assume getBookings requires auth
     // std::vector<Booking> bookings_after_logout = client.getBookings(); // This call would likely fail internally if auth is required


    std::cout << "\nApplication finished." << std::endl;

    return 0;
}