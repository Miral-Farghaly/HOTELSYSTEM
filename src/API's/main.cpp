// src/main.cpp
#include <iostream>     // For standard input/output (cout, cin, cerr)
#include <vector>       // For std::vector
#include <string>       // For std::string
#include <cstdlib>      // For std::getenv
#include <limits>       // For std::numeric_limits (used for clearing cin buffer)
#include <dotenv.h>     // For loading .env file
#include "ApiClient.h"  // Our API client class
#include "DataStructures.h" // Our data structures (Room, BookingData, etc.)

// Helper function to get environment variable or return a default value
std::string getEnvVar(const std::string& key, const std::string& defaultValue) {
    // Attempt to get the environment variable using std::getenv
    const char* value = std::getenv(key.c_str());
    // If getenv returns nullptr, the variable wasn't found
    if (value == nullptr) {
        // Print a warning to the console indicating the fallback
        std::cerr << "[Config Warning] Environment variable '" << key
                  << "' not found. Using default value: '" << defaultValue << "'" << std::endl;
        return defaultValue; // Return the provided default value
    }
    // If found, convert the C-style string to std::string and return it
    return std::string(value);
}

// Function to safely get password input (basic, platform-dependent better)
// This is still NOT truly secure for hiding input in a standard console.
// Real applications need platform-specific terminal control (termios on Linux/macOS, conio.h on Windows).
std::string getPasswordInput() {
    std::string password;
    // In a real app, disable echo here using platform specific code
    // e.g. on Linux/macOS: system("stty -echo");
    std::cin >> password;
    // Re-enable echo after input
    // e.g. on Linux/macOS: system("stty echo");
    std::cout << std::endl; // Add a newline after hidden input
    return password;
}

// Helper to clear input buffer after reading with >>
void clearInputBuffer() {
     std::cin.ignore(std::numeric_limits<std::streamsize>::max(), '\n');
}

int main() {
    // --- Load .env file ---
    try {
        // Attempt to load variables from a `.env` file in the current directory
        // into the process's environment variables.
        dotenv::load_dotenv();
        std::cout << ".env file processed (if found)." << std::endl;
    } catch (const std::exception& e) {
        // If dotenv::load_dotenv() throws (e.g., file permissions), log it.
        // It's often okay if the .env file doesn't exist, so this is a warning.
        std::cerr << "[Config Warning] Could not process .env file: " << e.what()
                  << ". Using environment variables or defaults." << std::endl;
    }
    // --- -------------- ---

    // --- Configuration ---
    // Get the API Base URL from the environment variable "API_BASE_URL".
    // If not found, use "http://127.0.0.1:8000/api" as the default.
    std::string api_base_url = getEnvVar("API_BASE_URL", "http://127.0.0.1:8000/api");

    // --- Initialize ApiClient ---
    // Create an instance of our ApiClient, passing the base URL.
    ApiClient client(api_base_url);
    std::optional<User> loggedInUser = std::nullopt; // Store logged in user details

    // --- Application Start ---
    std::cout << "\n--- Serene Hotel C++ Client ---" << std::endl;
    std::cout << "Connecting to API at: " << api_base_url << std::endl;

    // --- User Interaction Loop ---
    std::string command;
    while (true) {
        // Display options based on authentication state
        if (!client.isAuthenticated()) {
            std::cout << "\nOptions: [login, signup, exit]" << std::endl;
        } else {
            std::cout << "\nOptions: [rooms, my_bookings, create_booking, profile, logout, exit]" << std::endl;
        }
        std::cout << "> ";

        // Read the command, handling potential empty input
        if (!(std::cin >> command)) {
             std::cin.clear(); // Clear error flags
             clearInputBuffer();
             std::cerr << "Invalid input. Please try again." << std::endl;
             continue;
        }
        clearInputBuffer(); // Clear the rest of the line including newline

        // --- Command Handling ---

        if (command == "exit") {
            break; // Exit the main loop
        }
        // --- Unauthenticated Commands ---
        else if (command == "login" && !client.isAuthenticated()) {
            std::string email, password;
            std::cout << "Enter email: ";
            std::cin >> email;
            clearInputBuffer();
            std::cout << "Enter password: ";
            password = getPasswordInput(); // Use helper (basic security)
            // No need to clear buffer here, getPasswordInput handles it internally if needed

            if (client.login(email, password)) {
                std::cout << "\nLogin successful!" << std::endl;
                // --- Fetch logged-in user details (assuming login response includes user object) ---
                // This part assumes your ApiClient::login modifies itself or returns user data.
                // A better approach: modify ApiClient::login to return std::optional<User>
                // For now, we'll just fetch profile after login using a placeholder ID (NEEDS FIX)
                // loggedInUser = client.getUserProfile(SOME_USER_ID_FROM_LOGIN); // <<-- Ideal way
                std::cout << "Fetching user profile..." << std::endl; // Placeholder message
            } else {
                std::cerr << "\nLogin failed. Please check credentials or try again." << std::endl;
            }
        }
        else if (command == "signup" && !client.isAuthenticated()) {
             std::string username, email, password, phone;
             int age;
             std::cout << "Enter username: "; std::cin >> username; clearInputBuffer();
             std::cout << "Enter email: "; std::cin >> email; clearInputBuffer();
             std::cout << "Enter password: "; password = getPasswordInput(); // Already adds newline
             // No need to clear buffer here
             std::cout << "Enter phone: "; std::cin >> phone; clearInputBuffer();
             std::cout << "Enter age: ";
             while (!(std::cin >> age)) { // Input validation for age
                 std::cerr << "Invalid age. Please enter a number: ";
                 std::cin.clear();
                 clearInputBuffer();
             }
             clearInputBuffer();

             if (client.signup(username, email, password, phone, age)) {
                  std::cout << "\nSignup successful! You may need to login." << std::endl;
             } else {
                  std::cerr << "\nSignup failed. Please check details or email might be taken." << std::endl;
             }
        }
        // --- Authenticated Commands ---
        else if (command == "rooms" && client.isAuthenticated()) {
            std::cout << "\nFetching available rooms..." << std::endl;
            std::vector<Room> rooms = client.getRooms();
            if (!rooms.empty()) {
                std::cout << "--- Available Rooms ---" << std::endl;
                for (const auto& room : rooms) {
                    std::cout << "ID: " << room.id << " | Name: " << room.name
                              << " | Type: " << room.type << " | Price: $" << room.price
                              << " | Capacity: " << room.capacity << " | View: " << room.view << std::endl;
                    std::cout << "  Bed Size: " << room.bedSize << std::endl;
                    std::cout << "  Amenities: ";
                    for(size_t i = 0; i < room.amenities.size(); ++i) {
                        std::cout << room.amenities[i] << (i == room.amenities.size() - 1 ? "" : ", ");
                    }
                    std::cout << std::endl;
                    std::cout << "  Description: " << room.description << std::endl;
                    std::cout << "------------------------" << std::endl;
                }
            } else {
                std::cerr << "Failed to fetch rooms or no rooms currently listed." << std::endl;
            }
        }
        else if ((command == "my_bookings" || command == "bookings") && client.isAuthenticated()) { // Allow "bookings" too
             std::cout << "\nFetching your bookings..." << std::endl;
             std::vector<Booking> bookings = client.getBookings(); // Assumes API correctly returns bookings for the authenticated user
             if (!bookings.empty()) {
                 std::cout << "--- Your Bookings ---" << std::endl;
                 for(const auto& booking : bookings) {
                      std::cout << "Booking ID: " << booking.id << " | Room ID: " << booking.roomId
                                << " | Check-In: " << booking.checkIn << " | Check-Out: " << booking.checkOut
                                << " | Guests: " << booking.guests << std::endl;
                      std::cout << "  Status: " << booking.status << " | Package: " << booking.package
                                << " | Price: $" << booking.totalPrice << std::endl;
                      std::cout << "  Housekeeping: " << (booking.housekeeping ? ("Yes (" + booking.housekeepingTime + ")") : "No")
                                << " | Parking: " << (booking.parking ? "Yes" : "No") << std::endl;
                      std::cout << "---------------------" << std::endl;
                 }
             } else {
                  std::cout << "You currently have no bookings or failed to fetch them." << std::endl;
             }
        }
        else if (command == "create_booking" && client.isAuthenticated()) {
             BookingData newBooking;
             // Validate Room ID input
             std::cout << "Enter Room ID to book: ";
             while (!(std::cin >> newBooking.room_id)) {
                 std::cerr << "Invalid Room ID. Please enter a number: ";
                 std::cin.clear(); clearInputBuffer();
             } clearInputBuffer();

             // TODO: Add date validation (format, check-in before check-out)
             std::cout << "Enter Check-in Date (YYYY-MM-DD): "; std::cin >> newBooking.check_in; clearInputBuffer();
             std::cout << "Enter Check-out Date (YYYY-MM-DD): "; std::cin >> newBooking.check_out; clearInputBuffer();

             // Validate Guests input
             std::cout << "Enter Number of Guests: ";
             while (!(std::cin >> newBooking.guests) || newBooking.guests <= 0) {
                  std::cerr << "Invalid number of guests. Please enter a positive number: ";
                  std::cin.clear(); clearInputBuffer();
             } clearInputBuffer();

             std::cout << "Enter Package (e.g., Silver, Gold, Platinum): "; std::cin >> newBooking.package; clearInputBuffer();

             // Validate boolean inputs
             int tempBool;
             std::cout << "Request Housekeeping (1 for yes, 0 for no): ";
             while (!(std::cin >> tempBool) || (tempBool != 0 && tempBool != 1)) {
                  std::cerr << "Invalid input. Enter 1 for yes or 0 for no: ";
                  std::cin.clear(); clearInputBuffer();
             } clearInputBuffer();
             newBooking.housekeeping = (tempBool == 1);

             if(newBooking.housekeeping) {
                 std::cout << "Enter Preferred Housekeeping Time (HH:MM - e.g., 10:00): "; std::cin >> newBooking.housekeeping_time; clearInputBuffer();
             } else {
                 newBooking.housekeeping_time = ""; // Or pass nullopt if API handles it
             }

             std::cout << "Request Parking (1 for yes, 0 for no): ";
              while (!(std::cin >> tempBool) || (tempBool != 0 && tempBool != 1)) {
                  std::cerr << "Invalid input. Enter 1 for yes or 0 for no: ";
                  std::cin.clear(); clearInputBuffer();
             } clearInputBuffer();
             newBooking.parking = (tempBool == 1);

             std::cout << "\nAttempting to create booking..." << std::endl;
             auto createdBookingOpt = client.createBooking(newBooking);
             if (createdBookingOpt) {
                std::cout << "Booking request submitted successfully! Details:" << std::endl;
                const auto& booking = createdBookingOpt.value();
                std::cout << "  Booking ID: " << booking.id << std::endl;
                std::cout << "  Status: " << booking.status << std::endl; // Often 'pending' initially
                std::cout << "  Total Price: $" << booking.totalPrice << std::endl; // Price calculated by backend
             } else {
                std::cerr << "Booking creation failed. Please check the details or room availability for the selected dates." << std::endl;
             }
         }
        else if (command == "profile" && client.isAuthenticated()) {
             // --- Get Logged-in User ID ---
             // This is still a placeholder. The ID should be retrieved upon successful login.
             // Modify ApiClient::login to return or store the User object containing the ID.
             int userIdToFetch = 1; // <<< NEEDS TO BE REPLACED WITH ACTUAL LOGGED-IN USER ID
             if (loggedInUser.has_value()) { // Ideally use the stored user
                 userIdToFetch = loggedInUser.value().id;
                 std::cout << "\nFetching your profile (ID: " << userIdToFetch << ")..." << std::endl;
             } else {
                 std::cerr << "\nWarning: Could not determine logged-in user ID. Fetching placeholder ID " << userIdToFetch << "..." << std::endl;
             }


             auto userOpt = client.getUserProfile(userIdToFetch);
             if(userOpt) {
                 loggedInUser = userOpt; // Store fetched profile data
                 const auto& user = loggedInUser.value();
                 std::cout << "--- Your Profile ---" << std::endl;
                 std::cout << "ID: " << user.id << std::endl;
                 std::cout << "Username: " << user.username << std::endl;
                 std::cout << "Email: " << user.email << std::endl;
                 std::cout << "Phone: " << user.phone << std::endl;
                 std::cout << "Age: " << user.age << std::endl;
                 std::cout << "Role: " << user.role << std::endl;
                 std::cout << "--------------------" << std::endl;
             } else {
                  std::cerr << "Failed to fetch your user profile." << std::endl;
             }
        }
        else if (command == "logout" && client.isAuthenticated()) {
            std::cout << "\nLogging out..." << std::endl;
            client.logout(); // logout clears token locally
            loggedInUser = std::nullopt; // Clear stored user data
            std::cout << "You have been logged out." << std::endl;
        }
        // --- Invalid Command or State ---
        else {
            if (client.isAuthenticated()){
                 std::cerr << "Invalid command: '" << command << "'. Please choose from the available options." << std::endl;
            } else {
                 std::cerr << "Invalid command or action requires login. Please 'login' or 'signup'." << std::endl;
            }
        }
    } // End while loop

    // --- Application End ---
    std::cout << "\nExiting Hotel Client Application." << std::endl;

    // Attempt graceful logout if still authenticated upon exit
    if (client.isAuthenticated()) {
        std::cout << "Performing final logout..." << std::endl;
        client.logout();
    }

    return 0; // Indicate successful execution
}