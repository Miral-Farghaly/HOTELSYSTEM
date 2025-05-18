import React, { useState, useEffect } from 'react';

const RoomAvailabilityViewer = () => {
    const [rooms, setRooms] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filters, setFilters] = useState({
        type: 'all',
        minPrice: '',
        maxPrice: '',
        capacity: 'all'
    });

    useEffect(() => {
        fetchRooms();
    }, [filters]);

    const fetchRooms = async () => {
        try {
            const queryParams = new URLSearchParams({
                ...filters,
                type: filters.type === 'all' ? '' : filters.type,
                capacity: filters.capacity === 'all' ? '' : filters.capacity
            }).toString();

            const response = await fetch(`/api/rooms/availability?${queryParams}`);
            const data = await response.json();
            setRooms(data.rooms);
        } catch (error) {
            console.error('Error fetching rooms:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        setFilters(prev => ({
            ...prev,
            [name]: value
        }));
    };

    return (
        <div className="container mx-auto px-4 py-8">
            {/* Filters */}
            <div className="bg-white rounded-lg shadow p-6 mb-8">
                <h2 className="text-2xl font-bold mb-4">Room Availability</h2>
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <select
                        name="type"
                        value={filters.type}
                        onChange={handleFilterChange}
                        className="rounded-md border-gray-300"
                    >
                        <option value="all">All Types</option>
                        <option value="standard">Standard</option>
                        <option value="deluxe">Deluxe</option>
                        <option value="suite">Suite</option>
                    </select>

                    <input
                        type="number"
                        name="minPrice"
                        placeholder="Min Price"
                        value={filters.minPrice}
                        onChange={handleFilterChange}
                        className="rounded-md border-gray-300"
                    />

                    <input
                        type="number"
                        name="maxPrice"
                        placeholder="Max Price"
                        value={filters.maxPrice}
                        onChange={handleFilterChange}
                        className="rounded-md border-gray-300"
                    />

                    <select
                        name="capacity"
                        value={filters.capacity}
                        onChange={handleFilterChange}
                        className="rounded-md border-gray-300"
                    >
                        <option value="all">Any Capacity</option>
                        <option value="1">1 Person</option>
                        <option value="2">2 People</option>
                        <option value="4">4 People</option>
                    </select>
                </div>
            </div>

            {/* Room List */}
            {loading ? (
                <div className="flex justify-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {rooms.map(room => (
                        <div key={room.id} className="bg-white rounded-lg shadow overflow-hidden">
                            <img
                                src={room.image_url}
                                alt={room.name}
                                className="w-full h-48 object-cover"
                            />
                            <div className="p-6">
                                <h3 className="text-xl font-semibold mb-2">{room.name}</h3>
                                <p className="text-gray-600 mb-4">{room.description}</p>
                                <div className="flex justify-between items-center">
                                    <span className="text-2xl font-bold text-primary">
                                        ${room.price}/night
                                    </span>
                                    <span className={`px-3 py-1 rounded-full text-sm ${
                                        room.available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    }`}>
                                        {room.available ? 'Available' : 'Booked'}
                                    </span>
                                </div>
                                {room.available && (
                                    <button className="w-full mt-4 bg-primary text-white py-2 rounded-md hover:bg-primary-dark transition-colors">
                                        Book Now
                                    </button>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default RoomAvailabilityViewer; 