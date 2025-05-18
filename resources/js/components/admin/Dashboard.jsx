import React, { useState, useEffect } from 'react';
import { Line } from 'react-chartjs-2';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
);

const Dashboard = () => {
    const [stats, setStats] = useState(null);
    const [recentBookings, setRecentBookings] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchDashboardData();
    }, []);

    const fetchDashboardData = async () => {
        try {
            const [statsResponse, bookingsResponse] = await Promise.all([
                fetch('/api/admin/stats'),
                fetch('/api/admin/recent-bookings')
            ]);

            const [statsData, bookingsData] = await Promise.all([
                statsResponse.json(),
                bookingsResponse.json()
            ]);

            setStats(statsData);
            setRecentBookings(bookingsData);
        } catch (error) {
            console.error('Error fetching dashboard data:', error);
        } finally {
            setLoading(false);
        }
    };

    const bookingChartData = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [
            {
                label: 'Bookings',
                data: stats?.monthly_bookings || [],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }
        ]
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center h-screen">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>
        );
    }

    return (
        <div className="container mx-auto px-4 py-8">
            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-gray-500 text-sm">Total Bookings</h3>
                    <p className="text-3xl font-bold">{stats?.total_bookings}</p>
                    <span className="text-green-500 text-sm">
                        +{stats?.booking_increase}% from last month
                    </span>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-gray-500 text-sm">Revenue</h3>
                    <p className="text-3xl font-bold">${stats?.total_revenue}</p>
                    <span className="text-green-500 text-sm">
                        +{stats?.revenue_increase}% from last month
                    </span>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-gray-500 text-sm">Available Rooms</h3>
                    <p className="text-3xl font-bold">{stats?.available_rooms}</p>
                    <span className="text-gray-500 text-sm">
                        out of {stats?.total_rooms} total
                    </span>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-gray-500 text-sm">Active Users</h3>
                    <p className="text-3xl font-bold">{stats?.active_users}</p>
                    <span className="text-green-500 text-sm">
                        +{stats?.user_increase}% from last month
                    </span>
                </div>
            </div>

            {/* Booking Chart */}
            <div className="bg-white rounded-lg shadow p-6 mb-8">
                <h3 className="text-xl font-semibold mb-4">Booking Trends</h3>
                <div className="h-64">
                    <Line data={bookingChartData} options={{ maintainAspectRatio: false }} />
                </div>
            </div>

            {/* Recent Bookings */}
            <div className="bg-white rounded-lg shadow overflow-hidden">
                <div className="p-6">
                    <h3 className="text-xl font-semibold mb-4">Recent Bookings</h3>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Guest
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Room
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Check In
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Check Out
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {recentBookings.map((booking) => (
                                <tr key={booking.id}>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="flex items-center">
                                            <div className="ml-4">
                                                <div className="text-sm font-medium text-gray-900">
                                                    {booking.guest_name}
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    {booking.guest_email}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm text-gray-900">{booking.room_number}</div>
                                        <div className="text-sm text-gray-500">{booking.room_type}</div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm text-gray-900">
                                            {new Date(booking.check_in).toLocaleDateString()}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm text-gray-900">
                                            {new Date(booking.check_out).toLocaleDateString()}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                            booking.status === 'confirmed'
                                                ? 'bg-green-100 text-green-800'
                                                : booking.status === 'pending'
                                                ? 'bg-yellow-100 text-yellow-800'
                                                : 'bg-red-100 text-red-800'
                                        }`}>
                                            {booking.status}
                                        </span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};

export default Dashboard; 