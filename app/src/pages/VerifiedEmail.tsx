import { useSearchParams } from 'react-router-dom';
import { useEffect, useState } from 'react';
import axios from 'axios';

import { PulseLoader } from 'react-spinners';
import { CheckCircle2, LogIn, RefreshCcw, XCircle, Home } from 'lucide-react';
import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { useNavigate } from 'react-router-dom';
import Swal from 'sweetalert2';

const EmailVerifiedPage = () => {
    const navigate = useNavigate();
    const [isVerified, setIsVerified] = useState<boolean>(false);
    const [searchParams] = useSearchParams();
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string>();

    useEffect(() => {
        const verifyToken = async () => {
            const token = searchParams.get('token');

            // 1. Guard Clause: If no token exists in the URL
            if (!token) {
                setError("No verification token found.");
                setIsLoading(false);
                return;
            }

            try {
                // 2. The POST request to your PHP API
                // Using the environment variable you mentioned
                const response = await axios.post(import.meta.env.VITE_API_VERIFIED_EMAIL, {
                    token: token
                });

                // 3. Status OK handle
                // Axios enters the try block only if status is 2xx
                if (response.status === 200) {
                    setIsVerified(true);
                }
            } catch (error: any) {
                // 4. Error Path
                const message = error.response?.data?.message || "Verification failed or link expired.";
                setError(message);
            } finally {
                setIsLoading(false);
            }
        };

        //Fire action.
        verifyToken();
    }, [searchParams]);

    const requestNewLink = async () => {
        const email = searchParams.get('email');

        try {
            await axios.post(import.meta.env.VITE_API_VERIFY_EMAIL_REQUEST, {
                email: email
            });
        } catch (error: any) {
            if (error.status === 409) {
                const pressReturn = await Swal.fire({
                    icon: "error",
                    title: "ERROR",
                    text: error.response.data.message,
                    confirmButtonText: 'Return to Home',
                    confirmButtonColor: '#2563eb',
                    allowOutsideClick: false,
                    background: '#ffffff',
                });
                if (pressReturn.isConfirmed) navigate("/");

            } else {
                console.error(`Request: ${error.response.data.message}`);
                navigate(`/verify-email-request?email=${email}`);
            }
        }

    }//Request new link.

    if (isLoading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-slate-50 p-4">
                <PulseLoader color='#6082B6' />
            </div>
        );
    }

    if (!isVerified) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-slate-50 p-4">
                <Card className="w-full max-w-md shadow-xl border-t-4 border-t-red-500">
                    <CardHeader className="flex flex-col items-center">
                        <div className="h-16 w-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                            <XCircle className="h-10 w-10 text-red-600" />
                        </div>
                        <CardTitle className="text-2xl font-bold text-slate-900">Verification Failed</CardTitle>
                        <CardDescription className="text-center text-base pt-2">
                            {error ?? "Something went wrong during the verification process."}
                        </CardDescription>
                    </CardHeader>

                    <CardContent className="text-center">
                        <p className="text-sm text-muted-foreground">
                            This link may have expired or has already been used. Please try requesting a new verification link.
                        </p>
                    </CardContent>

                    <CardFooter className="flex flex-col gap-3">
                        <Button
                            variant="default"
                            className="w-full bg-slate-900"
                            onClick={requestNewLink}
                        >
                            <RefreshCcw className="mr-2 h-4 w-4" />
                            Request New Link
                        </Button>

                        <Button
                            variant="ghost"
                            className="w-full"
                            onClick={() => navigate('/')}
                        >
                            <LogIn className="ml-2 h-4 w-4" />
                            Back to Login
                        </Button>
                    </CardFooter>
                </Card>
            </div >
        );
    }
    if (isVerified) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-slate-50 p-4">
                <Card className="w-full max-w-md shadow-xl border-t-4 border-t-green-500">
                    <CardHeader className="space-y-1 flex flex-col items-center">
                        <div className="h-16 w-16 bg-green-100 rounded-full flex items-center justify-center mb-4 animate-in zoom-in duration-500">
                            <CheckCircle2 className="h-10 w-10 text-green-600" />
                        </div>
                        <CardTitle className="text-2xl font-bold text-slate-900">Email Verified!</CardTitle>
                        <CardDescription className="text-center text-base">
                            Your account has been successfully verified.
                            You now have full access to all features.
                        </CardDescription>
                    </CardHeader>

                    <CardContent className="pt-4">
                        <div className="bg-slate-100 rounded-lg p-4 mb-2">
                            <p className="text-sm text-slate-600 text-center">
                                Welcome to the <strong>User mangement system</strong> project.
                                You can now access my website.
                            </p>
                        </div>
                    </CardContent>

                    <CardFooter className="flex flex-col gap-3">
                        <Button
                            className="w-full bg-green-600 hover:bg-green-700"
                            onClick={() => navigate('/')}
                        >
                            Go Back to Login
                            <LogIn className="ml-2 h-4 w-4" />
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        );
    }
};

export default EmailVerifiedPage;