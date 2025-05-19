import React from 'react';
import { Routes, Route } from 'react-router-dom';

const App: React.FC = () => {
    return (
        <Routes>
            <Route path="/" element={<div>Welcome to Hotel Management System</div>} />
        </Routes>
    );
};

export default App; 