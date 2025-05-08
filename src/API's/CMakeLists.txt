cmake_minimum_required(VERSION 3.15)
project(HotelClient LANGUAGES CXX)

set(CMAKE_CXX_STANDARD 17)
set(CMAKE_CXX_STANDARD_REQUIRED ON)

# --- vcpkg ---
set(CMAKE_TOOLCHAIN_FILE "<path/to/your/vcpkg>/scripts/buildsystems/vcpkg.cmake"
    CACHE STRING "Vcpkg toolchain file") # *** UPDATE THIS PATH ***

# --- Find Packages ---
find_package(cpr CONFIG REQUIRED)
find_package(nlohmann_json CONFIG REQUIRED)
find_package(dotenv-cpp CONFIG REQUIRED) # <--- ADD THIS

# --- Add Executable with ALL source files ---
add_executable(hotel_client
    src/main.cpp
    src/ApiClient.cpp          # Core helpers
    src/ApiClient_Auth.cpp     # Auth implementations
    src/ApiClient_Rooms.cpp    # Room implementations
    src/ApiClient_Bookings.cpp # Booking implementations
    src/ApiClient_User.cpp     # User implementations
)

# --- Link Libraries ---
target_link_libraries(hotel_client PRIVATE
    cpr::cpr
    nlohmann_json::nlohmann_json
    dotenv-cpp::dotenv-cpp       # <--- ADD THIS
)