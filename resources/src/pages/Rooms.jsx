import React from 'react';
import { Link } from 'react-router-dom';

const rooms = [
  {
    id: 1,
    name: 'Deluxe Room',
    description: 'Spacious room with city view',
    price: 200,
    image: 'https://placehold.co/600x400',
  },
  {
    id: 2,
    name: 'Suite',
    description: 'Luxury suite with separate living area',
    price: 350,
    image: 'https://placehold.co/600x400',
  },
  {
    id: 3,
    name: 'Family Room',
    description: 'Perfect for families, includes 2 queen beds',
    price: 300,
    image: 'https://placehold.co/600x400',
  },
  // Add more rooms as needed
];

function Rooms() {
  return (
    <div className="bg-white">
      <div className="max-w-2xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:max-w-7xl lg:px-8">
        <h2 className="text-3xl font-extrabold text-gray-900 mb-8">Available Rooms</h2>
        <div className="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-3 xl:gap-x-8">
          {rooms.map((room) => (
            <div key={room.id} className="group relative">
              <div className="w-full min-h-80 bg-gray-200 aspect-w-1 aspect-h-1 rounded-md overflow-hidden group-hover:opacity-75 lg:h-80 lg:aspect-none">
                <img
                  src={room.image}
                  alt={room.name}
                  className="w-full h-full object-center object-cover lg:w-full lg:h-full"
                />
              </div>
              <div className="mt-4 flex justify-between">
                <div>
                  <h3 className="text-sm text-gray-700">
                    <Link to={`/booking?room=${room.id}`}>
                      <span aria-hidden="true" className="absolute inset-0" />
                      {room.name}
                    </Link>
                  </h3>
                  <p className="mt-1 text-sm text-gray-500">{room.description}</p>
                </div>
                <p className="text-sm font-medium text-gray-900">${room.price}/night</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

export default Rooms; 