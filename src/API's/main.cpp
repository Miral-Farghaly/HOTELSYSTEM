// src/main.cpp
#include <iostream>     // For standard input/output (cout, cin, cerr)
#include <vector>       // For std::vector
#include <string>       // For std::string
#include <cstdlib>      // For std::getenv
#include <limits>       // For std::numeric_limits (used for clearing cin buffer)
#include <optional>     // For std::optional
#include <dotenv.h>     // For loading .env file
#include "ApiClient.h"  // Our API client class
#include "DataStructures.h" // Our data structures (Room, BookingData, etc.)

// Helper function to get environment variable or return a default value
std::string getEnvVar(const std::string& key, const std::string& defaultValue) {
    const char* value = std::getenv(key.c_str());
    if (value == nullptr) {
        std::cerr << "[Config Warning] Environment variable '" << key
                  << "' not found. Using default value: '" << defaultValue << "'" << std::endl;
        return defaultValue;
    }
    return std::string(value);
}

// Function to safely get password input (basic console security)
// WARNING: This is NOT truly secure for hiding input in standard console.
// Real applications need platform-specific terminal control.
std::string getPasswordInput() {
    std::string password;
    // Placeholder for platform-specific code to disable terminal echo
    // e.g. on Linux/macOS: system("stty -echo");
    std::cout << std::flush; // Ensure prompt is displayed before reading
    std::cin >> password;
    // Placeholder for platform-specific code to re-enable terminal echo
    // e.g. on Linux/macOS: system("stty echo");
    std::cout << std::endl; // Add a newline after potentially hidden input
    return password;
}

// Helper to clear input buffer after reading with >> or getline issues
void clearInputBuffer() {
     // Set the stream state back to good if it entered a fail state (e.g., non-numeric input for int)
     if(std::cin.fail()){
        std::cin.clear();
     }
     // Ignore all characters up to and including the next newline character
     std::cin.ignore(std::numeric_limits<std::streamsize>::max(), '\n');
}

int main() {
    // --- Load .env file ---
    try {
        dotenv::load_dotenv();
        std::cout << ".env file processed (if found)." << std::endl;
    } catch (const std::exception& e) {
        std::cerr << "[Config Warning] Could not process .env file: " << e.what()
                  << ". Using environment variables or defaults." << std::endl;
    }
    // --- -------------- ---

    // --- Configuration ---
    std::string api_base_url = getEnvVar("API_BASE_URL", "http://127.0.0.1:8000/api");

    // --- Initialize ApiClient ---
    ApiClient client(api_base_url);
    std::optional<User> loggedInUser = std::nullopt; // Store logged in user details

    // --- Application Start ---
    std::cout << "\n--- Serene Hotel C++ Client ---" << std::endl;
    std::cout << "Connecting to API at: " << api_base_url << std::endl;

    // --- User Interaction Loop ---
    std::string command;
    while (true) {
        // Display options based on authentication state
        if (!loggedInUser.has_value()) { // Check if user is logged in
            std::cout << "\nOptions: [login, signup, exit]" << std::endl;
        } else {
            std::cout << "\nLogged in as: " << loggedInUser.value().username << " (Role: " << loggedInUser.value().role << ")" << std::endl;
            std::cout << "Options: [rooms, my_bookings, create_booking, profile, logout";
            // Add manager options if applicable
            if (loggedInUser.value().role == "manager" || loggedInUser.value().role == "receptionist") { // Adjust roles as needed
                 std::cout << ", create_room, update_room, delete_room";
            }
            std::cout << ", exit]" << std::endl;
        }
        std::cout << "> ";

        // Read the command
        if (!(std::cin >> command)) {
             if (std::cin.eof()) break; // Exit loop on EOF (Ctrl+D)
             std::cerr << "Invalid input. Please try again." << std::endl;
             clearInputBuffer();
             continue;
        }
        clearInputBuffer(); // Clear the rest of the line including newline

        // --- Command Handling ---

        if (command == "exit") {
            break; // Exit the main loop
        }
        // --- Unauthenticated Commands ---
        else if (command == "login" && !loggedInUser) {
            std::string email, password;
            std::cout << "Enter email: ";
            std::cin >> email; clearInputBuffer();
            std::cout << "Enter password: ";
            password = getPasswordInput(); // Use helper

            std::optional<User> loginResult = client.login(email, password);
            if (loginResult.has_value()) {
                loggedInUser = loginResult; // Store the returned User object
                std::cout << "\nLogin successful! Welcome, " << loggedInUser.value().username << "." << std::endl;
            } else {
                std::cerr << "\nLogin failed. Please check credentials or try again." << std::endl;
            }
        }
        else if (command == "signup" && !loggedInUser) {
             std::string username, email, password, phone; int age;
             std::cout << "Enter username: "; std::cin >> username; clearInputBuffer();
             std::cout << "Enter email: "; std::cin >> email; clearInputBuffer();
             std::cout << "Enter password: "; password = getPasswordInput();
             std::cout << "Enter phone: "; std::cin >> phone; clearInputBuffer();
             std::cout << "Enter age: ";
             while (!(std::cin >> age) || age <=0) { std::cerr << "Invalid age: "; std::cin.clear(); clearInputBuffer(); } clearInputBuffer();

             if (client.signup(username, email, password, phone, age)) {
                  std::cout << "\nSignup successful! Please login." << std::endl;
             } else {
                  std::cerr << "\nSignup failed. Please check details or email might be taken." << std::endl;
             }
        }
        // --- Authenticated Commands ---
        else if (command == "rooms" && loggedInUser) {
            std::cout << "\nFetching available rooms..." << std::endl;
            std::vector<Room> rooms = client.getRooms();
            if (!rooms.empty()) {
                std::cout << "--- Available Rooms ---" << std::endl;
                for (const auto& room : rooms) {
                    std::cout << "ID: " << room.id << " | Name: " << room.name
                              << " | Type: " << room.type << " | Price: $" << room.price
                              << " | Capacity: " << room.capacity << " | View: " << room.view
                              << " | Available: " << (room.available ? "Yes" : "No") << std::endl;
                    std::cout << "  Bed Size: " << room.bedSize << std::endl;
                    std::cout << "  Amenities: ";
                    for(size_t i = 0; i < room.amenities.size(); ++i) { std::cout << room.amenities[i] << (i == room.amenities.size() - 1 ? "" : ", "); }
                    std::cout << std::endl;
                    std::cout << "  Description: " << room.description << std::endl;
                    std::cout << "------------------------" << std::endl;
                }
            } else {
                std::cerr << "Failed to fetch rooms or no rooms currently listed." << std::endl;
            }
        }
        else if ((command == "my_bookings" || command == "bookings") && loggedInUser) {
             std::cout << "\nFetching your bookings..." << std::endl;
             std::vector<Booking> bookings = client.getBookings(); // Backend handles filtering by auth token
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
        else if (command == "create_booking" && loggedInUser) {
             BookingData newBooking; int tempBool;
             std::cout << "Enter Room ID to book: ";
             while (!(std::cin >> newBooking.room_id)) { std::cerr << "Invalid ID: "; std::cin.clear(); clearInputBuffer();} clearInputBuffer();
             std::cout << "Enter Check-in Date (YYYY-MM-DD): "; std::cin >> newBooking.check_in; clearInputBuffer(); // Add validation
             std::cout << "Enter Check-out Date (YYYY-MM-DD): "; std::cin >> newBooking.check_out; clearInputBuffer(); // Add validation
             std::cout << "Enter Number of Guests: ";
             while (!(std::cin >> newBooking.guests) || newBooking.guests <= 0) { std::cerr << "Invalid guests: "; std::cin.clear(); clearInputBuffer();} clearInputBuffer();
             std::cout << "Enter Package (e.g., Silver, Gold, Platinum): "; std::cin >> newBooking.package; clearInputBuffer();
             std::cout << "Request Housekeeping (1=yes, 0=no): ";
             while (!(std::cin >> tempBool) || (tempBool != 0 && tempBool != 1)) { std::cerr << "Invalid (1/0): "; std::cin.clear(); clearInputBuffer();} clearInputBuffer();
             newBooking.housekeeping = (tempBool == 1);
             if(newBooking.housekeeping) { std::cout << "Enter Preferred HK Time (HH:MM): "; std::cin >> newBooking.housekeeping_time; clearInputBuffer(); } else { newBooking.housekeeping_time = ""; }
             std::cout << "Request Parking (1=yes, 0=no): ";
             while (!(std::cin >> tempBool) || (tempBool != 0 && tempBool != 1)) { std::cerr << "Invalid (1/0): "; std::cin.clear(); clearInputBuffer();} clearInputBuffer();
             newBooking.parking = (tempBool == 1);

             std::cout << "\nAttempting to create booking..." << std::endl;
             auto createdBookingOpt = client.createBooking(newBooking);
             if (createdBookingOpt) {
                std::cout << "Booking request submitted successfully! Details:" << std::endl;
                const auto& booking = createdBookingOpt.value();
                std::cout << "  Booking ID: " << booking.id << std::endl;
                std::cout << "  Status: " << booking.status << std::endl;
                std::cout << "  Total Price: $" << booking.totalPrice << std::endl;
             } else {
                std::cerr << "Booking creation failed. Please check details or room availability." << std::endl;
             }
         }
        else if (command == "profile" && loggedInUser) {
            int userIdToFetch = loggedInUser.value().id; // Get ID from stored object
            std::cout << "\nFetching your profile (ID: " << userIdToFetch << ")..." << std::endl;

            auto userOpt = client.getUserProfile(userIdToFetch);
            if(userOpt) {
                loggedInUser = userOpt; // Update local copy
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
         // --- Staff/Manager Room Commands ---
         else if (command == "create_room" && loggedInUser && (loggedInUser.value().role == "manager" || loggedInUser.value().role == "receptionist")) {
             RoomData newRoom;
             std::cout << "Enter Room Name: "; std::getline(std::cin, newRoom.name); // Use getline for names with spaces
             std::cout << "Enter Room Type: "; std::cin >> newRoom.type; clearInputBuffer();
             std::cout << "Enter Price per Night: "; while (!(std::cin >> newRoom.price) || newRoom.price <0) { std::cerr << "Invalid price: "; std::cin.clear(); clearInputBuffer(); } clearInputBuffer();
             std::cout << "Enter Bed Size: "; std::getline(std::cin, newRoom.bed_size); // Use getline
             std::cout << "Enter View: "; std::getline(std::cin, newRoom.view); // Use getline
             std::cout << "Enter Capacity: "; while (!(std::cin >> newRoom.capacity) || newRoom.capacity <= 0) { std::cerr << "Invalid capacity: "; std::cin.clear(); clearInputBuffer(); } clearInputBuffer();
             std::cout << "Enter Description: "; std::getline(std::cin, newRoom.description); // Use getline
             std::cout << "Enter Image URL (optional): "; std::getline(std::cin, newRoom.image); // Use getline
             std::cout << "Enter Amenities (comma-separated, e.g., Wifi,TV): ";
             std::string amenitiesStr; std::getline(std::cin, amenitiesStr); // Use getline
             // Basic comma splitting (robust parsing needed for real app)
             std::string temp;
             for (char c : amenitiesStr) {
                if (c == ',') { if (!temp.empty()) newRoom.amenities.push_back(temp); temp.clear(); }
                else if (!std::isspace(c)) { temp += c; }
             }
             if (!temp.empty()) newRoom.amenities.push_back(temp);
             int avail; std::cout << "Is Available (1=yes, 0=no): "; while (!(std::cin >> avail) || (avail != 0 && avail != 1)) { std::cerr << "Invalid (1/0): "; std::cin.clear(); clearInputBuffer(); } clearInputBuffer();
             newRoom.available = (avail == 1);

             std::cout << "\nCreating room..." << std::endl;
             auto createdRoomOpt = client.createRoom(newRoom);
             if(createdRoomOpt) { std::cout << "Room created successfully! ID: " << createdRoomOpt.value().id << std::endl; }
             else { std::cerr << "Failed to create room." << std::endl; }
         }
         else if (command == "update_room" && loggedInUser && (loggedInUser.value().role == "manager" || loggedInUser.value().role == "receptionist")) {
              int roomId; RoomData updatedRoom;
              std::cout << "Enter ID of room to update: "; while (!(std::cin >> roomId)) { std::cerr << "Invalid ID: "; std::cin.clear(); clearInputBuffer(); } clearInputBuffer();
              // Fetch existing data first? Or just provide all fields again:
             std::cout << "Enter NEW Room Name: "; std::getline(std::cin, updatedRoom.name);
             std::cout << "Enter NEW Room Type: "; std::cin >> updatedRoom.type; clearInputBuffer();
             std::cout << "Enter NEW Price per Night: "; while (!(std::cin >> updatedRoom.price) || updatedRoom.price <0) { std::cerr << "Invalid price: "; std::cin.clear(); clearInputBuffer(); } clearInputBuffer();
             std::cout << "Enter NEW Bed Size: "; std::getline(std::cin, updatedRoom.bed_size);
             std::cout << "Enter NEW View: "; std::getline(std::cin, updatedRoom.view);
             std::cout << "Enter NEW Capacity: "; while (!(std::cin >> updatedRoom.capacity) || updatedRoom.capacity <= 0) { std::cerr << "Invalid capacity: "; std::cin.clear(); clearInputBuffer(); } clearInputBuffer();
             std::cout << "Enter NEW Description: "; std::getline(std::cin, updatedRoom.description);
             std::cout << "Enter NEW Image URL: "; std::getline(std::cin, updatedRoom.image);
             std::cout << "Enter NEW Amenities (comma-separated): ";
             std::string amenitiesStr; std::getline(std::cin, amenitiesStr);
             updatedRoom.amenities.clear(); // Clear old if updating
             std::string temp;
             for (char c : amenitiesStr) { /* ... split comma separated ... */ if(c==','){if(!temp.empty()) updatedRoom.amenities.push_back(temp); temp.clear();} else if (!std::isspace(c)) {temp += c;} } if(!temp.empty()) updatedRoom.amenities.push_back(temp);
             int avail; std::cout << "Set Available (1=yes, 0=no): "; while (!(std::cin >> avail) || (avail != 0 && avail != 1)) { std::cerr << "Invalid (1/0): "; std::cin.clear(); clearInputBuffer(); } clearInputBuffer();
             updatedRoom.available = (avail == 1);

             std::cout << "\nUpdating room " << roomId << "..." << std::endl;
             if(client.updateRoom(roomId, updatedRoom)) { std::cout << "Room updated successfully!" << std::endl; }
             else { std::cerr << "Failed to update room." << std::endl; }
         }
          else if (command == "delete_room" && loggedInUser && (loggedInUser.value().role == "manager" || loggedInUser.value().role == "receptionist")) {
              int roomId;
              std::cout << "Enter ID of room to DELETE: "; while (!(std::cin >> roomId)) { std::cerr << "Invalid ID: "; std::cin.clear(); clearInputBuffer(); } clearInputBuffer();
              std::cout << "Are you sure you want to delete room " << roomId << "? (yes/no): ";
              std::string confirm; std::cin >> confirm; clearInputBuffer();
              if (confirm == "yes") {
                  std::cout << "\nDeleting room " << roomId << "..." << std::endl;
                  if(client.deleteRoom(roomId)) { std::cout << "Room deleted successfully!" << std::endl; }
                  else { std::cerr << "Failed to delete room." << std::endl; }
              } else {
                   std::cout << "Deletion cancelled." << std::endl;
              }
         }
        else if (command == "logout" && loggedInUser) {
            std::cout << "\nLogging out..." << std::endl;
            client.logout();
            loggedInUser = std::nullopt; // Clear the stored user data
            std::cout << "You have been logged out." << std::endl;
        }
        // --- Invalid Command or State ---
        else {
            if (loggedInUser){
                 std::cerr << "Invalid command: '" << command << "'. Or insufficient permissions." << std::endl;
            } else {
                 std::cerr << "Invalid command or action requires login. Please 'login' or 'signup'." << std::endl;
            }
        }
    } // End while loop

    // --- Application End ---
    std::cout << "\nExiting Hotel Client Application." << std::endl;
    // Attempt graceful logout if user exits while still authenticated
    if (client.isAuthenticated()) {
        std::cout << "Performing final logout..." << std::endl;
        client.logout();
    }

    return 0; // Indicate successful execution
}