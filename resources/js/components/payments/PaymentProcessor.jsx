import React, { useState } from 'react';
import { loadStripe } from '@stripe/stripe-js';
import {
    CardElement,
    Elements,
    useStripe,
    useElements,
} from '@stripe/react-stripe-js';

// Initialize Stripe
const stripePromise = loadStripe(process.env.STRIPE_PUBLIC_KEY);

const PaymentForm = ({ amount, onSuccess, onError }) => {
    const stripe = useStripe();
    const elements = useElements();
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const handleSubmit = async (event) => {
        event.preventDefault();
        setLoading(true);
        setError(null);

        if (!stripe || !elements) {
            return;
        }

        try {
            // Create payment intent
            const { clientSecret } = await fetch('/api/payments/create-intent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ amount }),
            }).then(res => res.json());

            // Confirm payment
            const { error: stripeError, paymentIntent } = await stripe.confirmCardPayment(
                clientSecret,
                {
                    payment_method: {
                        card: elements.getElement(CardElement),
                    },
                }
            );

            if (stripeError) {
                setError(stripeError.message);
                onError?.(stripeError);
            } else if (paymentIntent.status === 'succeeded') {
                onSuccess?.(paymentIntent);
            }
        } catch (err) {
            setError('An error occurred while processing your payment.');
            onError?.(err);
        } finally {
            setLoading(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="max-w-md mx-auto">
            <div className="bg-white rounded-lg shadow p-6">
                <h3 className="text-xl font-semibold mb-4">Payment Details</h3>
                
                <div className="mb-6">
                    <label className="block text-gray-700 text-sm font-bold mb-2">
                        Card Information
                    </label>
                    <div className="border rounded-md p-3">
                        <CardElement
                            options={{
                                style: {
                                    base: {
                                        fontSize: '16px',
                                        color: '#424770',
                                        '::placeholder': {
                                            color: '#aab7c4',
                                        },
                                    },
                                    invalid: {
                                        color: '#9e2146',
                                    },
                                },
                            }}
                        />
                    </div>
                </div>

                {error && (
                    <div className="mb-4 p-3 bg-red-50 text-red-600 rounded-md">
                        {error}
                    </div>
                )}

                <div className="mb-4">
                    <p className="text-gray-700">
                        Amount to pay: <span className="font-bold">${amount}</span>
                    </p>
                </div>

                <button
                    type="submit"
                    disabled={!stripe || loading}
                    className={`w-full bg-primary text-white py-2 px-4 rounded-md ${
                        loading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-primary-dark'
                    }`}
                >
                    {loading ? (
                        <span className="flex items-center justify-center">
                            <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    ) : (
                        'Pay Now'
                    )}
                </button>
            </div>
        </form>
    );
};

const PaymentProcessor = ({ amount, onSuccess, onError }) => {
    return (
        <Elements stripe={stripePromise}>
            <PaymentForm
                amount={amount}
                onSuccess={onSuccess}
                onError={onError}
            />
        </Elements>
    );
};

export default PaymentProcessor; 