import React, { useState, useEffect } from 'react';

const UserProfile = () => {
    const [profile, setProfile] = useState({
        name: '',
        email: '',
        phone: '',
        address: '',
    });
    const [isEditing, setIsEditing] = useState(false);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);

    useEffect(() => {
        fetchUserProfile();
    }, []);

    const fetchUserProfile = async () => {
        try {
            const response = await fetch('/api/user/profile');
            const data = await response.json();
            setProfile(data);
        } catch (error) {
            setError('Failed to load profile');
        } finally {
            setLoading(false);
        }
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setProfile(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError(null);
        setSuccess(null);

        try {
            const response = await fetch('/api/user/profile', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(profile),
            });

            if (!response.ok) {
                throw new Error('Failed to update profile');
            }

            setSuccess('Profile updated successfully');
            setIsEditing(false);
        } catch (error) {
            setError(error.message);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
        );
    }

    return (
        <div className="max-w-2xl mx-auto p-4">
            <div className="bg-white rounded-lg shadow p-6">
                <div className="flex justify-between items-center mb-6">
                    <h2 className="text-2xl font-semibold">Profile Information</h2>
                    <button
                        onClick={() => setIsEditing(!isEditing)}
                        className="text-primary hover:text-primary-dark"
                    >
                        {isEditing ? 'Cancel' : 'Edit'}
                    </button>
                </div>

                {error && (
                    <div className="mb-4 p-3 bg-red-50 text-red-600 rounded-md">
                        {error}
                    </div>
                )}

                {success && (
                    <div className="mb-4 p-3 bg-green-50 text-green-600 rounded-md">
                        {success}
                    </div>
                )}

                <form onSubmit={handleSubmit}>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Name
                            </label>
                            <input
                                type="text"
                                name="name"
                                value={profile.name}
                                onChange={handleInputChange}
                                disabled={!isEditing}
                                className={`mt-1 block w-full rounded-md ${
                                    isEditing
                                        ? 'border-gray-300'
                                        : 'border-transparent bg-gray-50'
                                }`}
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Email
                            </label>
                            <input
                                type="email"
                                name="email"
                                value={profile.email}
                                onChange={handleInputChange}
                                disabled={!isEditing}
                                className={`mt-1 block w-full rounded-md ${
                                    isEditing
                                        ? 'border-gray-300'
                                        : 'border-transparent bg-gray-50'
                                }`}
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Phone
                            </label>
                            <input
                                type="tel"
                                name="phone"
                                value={profile.phone}
                                onChange={handleInputChange}
                                disabled={!isEditing}
                                className={`mt-1 block w-full rounded-md ${
                                    isEditing
                                        ? 'border-gray-300'
                                        : 'border-transparent bg-gray-50'
                                }`}
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Address
                            </label>
                            <textarea
                                name="address"
                                value={profile.address}
                                onChange={handleInputChange}
                                disabled={!isEditing}
                                rows="3"
                                className={`mt-1 block w-full rounded-md ${
                                    isEditing
                                        ? 'border-gray-300'
                                        : 'border-transparent bg-gray-50'
                                }`}
                            />
                        </div>

                        {isEditing && (
                            <div className="flex justify-end">
                                <button
                                    type="submit"
                                    disabled={loading}
                                    className={`bg-primary text-white px-4 py-2 rounded-md ${
                                        loading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-primary-dark'
                                    }`}
                                >
                                    {loading ? 'Saving...' : 'Save Changes'}
                                </button>
                            </div>
                        )}
                    </div>
                </form>

                {/* Booking History Section */}
                <div className="mt-8">
                    <h3 className="text-xl font-semibold mb-4">Booking History</h3>
                    <div className="border rounded-md divide-y">
                        {profile.bookings?.map((booking) => (
                            <div key={booking.id} className="p-4">
                                <div className="flex justify-between items-start">
                                    <div>
                                        <h4 className="font-medium">{booking.room_type}</h4>
                                        <p className="text-sm text-gray-600">
                                            {new Date(booking.check_in).toLocaleDateString()} - {new Date(booking.check_out).toLocaleDateString()}
                                        </p>
                                    </div>
                                    <span className={`px-2 py-1 rounded-full text-xs ${
                                        booking.status === 'completed'
                                            ? 'bg-green-100 text-green-800'
                                            : booking.status === 'upcoming'
                                            ? 'bg-blue-100 text-blue-800'
                                            : 'bg-gray-100 text-gray-800'
                                    }`}>
                                        {booking.status}
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default UserProfile; 