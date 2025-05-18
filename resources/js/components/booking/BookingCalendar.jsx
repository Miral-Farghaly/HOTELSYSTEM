import React, { useState, useEffect } from 'react';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';

const BookingCalendar = ({ roomId, onDateSelect }) => {
    const [startDate, setStartDate] = useState(null);
    const [endDate, setEndDate] = useState(null);
    const [availableDates, setAvailableDates] = useState([]);
    const [isLoading, setIsLoading] = useState(false);

    useEffect(() => {
        if (roomId) {
            fetchAvailability();
        }
    }, [roomId]);

    const fetchAvailability = async () => {
        setIsLoading(true);
        try {
            const response = await fetch(`/api/rooms/${roomId}/availability`);
            const data = await response.json();
            setAvailableDates(data.available_dates);
        } catch (error) {
            console.error('Error fetching availability:', error);
        }
        setIsLoading(false);
    };

    const handleDateChange = (dates) => {
        const [start, end] = dates;
        setStartDate(start);
        setEndDate(end);
        
        if (start && end && onDateSelect) {
            onDateSelect({ startDate: start, endDate: end });
        }
    };

    // Disable dates that are not available
    const isDateDisabled = (date) => {
        return !availableDates.some(availableDate => 
            date.toISOString().split('T')[0] === availableDate
        );
    };

    return (
        <div className="w-full max-w-md mx-auto">
            <div className="bg-white rounded-lg shadow p-6">
                <h3 className="text-lg font-semibold mb-4">Select Dates</h3>
                {isLoading ? (
                    <div className="flex justify-center">
                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    </div>
                ) : (
                    <DatePicker
                        selected={startDate}
                        onChange={handleDateChange}
                        startDate={startDate}
                        endDate={endDate}
                        selectsRange
                        inline
                        minDate={new Date()}
                        filterDate={isDateDisabled}
                        className="w-full"
                        dateFormat="yyyy-MM-dd"
                        placeholderText="Select check-in and check-out dates"
                    />
                )}
                
                {startDate && endDate && (
                    <div className="mt-4 p-4 bg-gray-50 rounded-md">
                        <p className="text-sm text-gray-600">
                            Check-in: {startDate.toLocaleDateString()}
                        </p>
                        <p className="text-sm text-gray-600">
                            Check-out: {endDate.toLocaleDateString()}
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
};

export default BookingCalendar; 