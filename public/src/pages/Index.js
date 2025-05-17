"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
// Update this page to show the demo credentials
const button_1 = require("@/components/ui/button");
const card_1 = require("@/components/ui/card");
const react_router_dom_1 = require("react-router-dom");
const Index = () => {
    const navigate = (0, react_router_dom_1.useNavigate)();
    return (<div className="min-h-screen flex flex-col items-center justify-center bg-gray-50 p-4">
      <card_1.Card className="w-full max-w-lg shadow-lg">
        <card_1.CardHeader className="text-center">
          <card_1.CardTitle className="text-3xl font-bold">Your Hotel</card_1.CardTitle>
          <card_1.CardDescription className="text-lg">Reservation System Demo</card_1.CardDescription>
        </card_1.CardHeader>
        <card_1.CardContent className="space-y-6">
          <div className="space-y-2">
            <h3 className="font-semibold text-lg">Demo Credentials</h3>
            
            <div className="bg-gray-100 p-3 rounded-md">
              <h4 className="font-medium">User (Guest):</h4>
              <ul className="list-disc list-inside pl-4 text-sm">
                <li>Email: guest@yourhotel.com</li>
                <li>Password: password123</li>
              </ul>
            </div>
            
            <div className="bg-gray-100 p-3 rounded-md">
              <h4 className="font-medium">Manager:</h4>
              <ul className="list-disc list-inside pl-4 text-sm">
                <li>Email: manager@yourhotel.com</li>
                <li>Password: managerpass</li>
              </ul>
            </div>
            
            <div className="bg-gray-100 p-3 rounded-md">
              <h4 className="font-medium">Receptionist:</h4>
              <ul className="list-disc list-inside pl-4 text-sm">
                <li>Email: receptionist@yourhotel.com</li>
                <li>Password: receptionpass</li>
              </ul>
            </div>
          </div>
          
          <p className="text-center text-sm text-gray-500">
            Click the button below to view the original JavaScript application
          </p>
        </card_1.CardContent>
        <card_1.CardFooter>
          <button_1.Button onClick={() => window.open('/', '_blank')} className="w-full">
            Open Hotel Reservation Demo
          </button_1.Button>
        </card_1.CardFooter>
      </card_1.Card>
    </div>);
};
exports.default = Index;
