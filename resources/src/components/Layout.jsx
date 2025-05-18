import React from 'react';
import { Outlet, Link } from 'react-router-dom';

function Layout() {
  return (
    <div className="min-h-screen bg-gray-100">
      <nav className="bg-white shadow-lg">
        <div className="max-w-7xl mx-auto px-4">
          <div className="flex justify-between h-16">
            <div className="flex">
              <Link to="/" className="flex items-center">
                <span className="text-xl font-bold">Hotel System</span>
              </Link>
              <div className="hidden md:flex items-center space-x-4 ml-10">
                <Link to="/rooms" className="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-gray-900">
                  Rooms
                </Link>
                <Link to="/booking" className="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-gray-900">
                  Book Now
                </Link>
              </div>
            </div>
            <div className="flex items-center space-x-4">
              <Link to="/login" className="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-gray-900">
                Login
              </Link>
              <Link to="/register" className="px-4 py-2 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                Register
              </Link>
            </div>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <Outlet />
      </main>

      <footer className="bg-white shadow-lg mt-auto">
        <div className="max-w-7xl mx-auto py-6 px-4">
          <p className="text-center text-gray-500">© 2024 Hotel System. All rights reserved.</p>
        </div>
      </footer>
    </div>
  );
}

export default Layout; 