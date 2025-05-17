
// Update this page to show the demo credentials
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { useNavigate } from "react-router-dom";

const Index = () => {
  const navigate = useNavigate();
  
  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-gray-50 p-4">
      <Card className="w-full max-w-lg shadow-lg">
        <CardHeader className="text-center">
          <CardTitle className="text-3xl font-bold">Your Hotel</CardTitle>
          <CardDescription className="text-lg">Reservation System Demo</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
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
        </CardContent>
        <CardFooter>
          <Button 
            onClick={() => window.open('/', '_blank')}
            className="w-full"
          >
            Open Hotel Reservation Demo
          </Button>
        </CardFooter>
      </Card>
    </div>
  );
};

export default Index;
