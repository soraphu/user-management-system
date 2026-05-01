import { useSearchParams } from 'react-router-dom';
import { useEffect, useState } from 'react';
import axios from 'axios';

import { PulseLoader } from 'react-spinners';
import { CheckCircle2, LogIn, RefreshCcw, XCircle } from 'lucide-react';
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
import { toast } from 'sonner';
import { getCatchMessage } from '@/handler/request_handler';

const EmailVerfied = () => {
    const navigate = useNavigate();
    const [isVerified, setIsVerified] = useState<boolean>(false);
    const [searchParams] = useSearchParams();
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string>();

    useEffect(() => {
        const verifyToken = async () => {
            const token = searchParams.get('token');

            // If no token exists in the URL
            if (!token) {
                setError("No verification token found.");
                setIsLoading(false);
                return;
            }

            try {
                // Using the environment variable you mentioned
                const response: any = await axios.post(import.meta.env.VITE_API_VERIFIED_EMAIL, {
                    token: token
                });

                // Axios enters the try block only if status is 2xx
                setIsVerified(true);
            } catch (error: any) {
                const errorMessage = getCatchMessage(error);
                setError(errorMessage);
            } finally {
                setIsLoading(false);
            }
        };

        //Fire action.
        verifyToken();
    }, [searchParams]);

    const swalNavigateOnConfirm = async ({ icon, title, text, confirmButtonText, navigateTo }: {
        icon: "success" | "error", title: string, text: string, confirmButtonText: string, navigateTo: string
    }) => {
        const dialog = await Swal.fire({
            icon: icon,
            title: title,
            text: text,
            confirmButtonText: confirmButtonText,
            confirmButtonColor: '#2563eb',
            allowOutsideClick: false,
            background: '#ffffff',
        });
        if (dialog.isConfirmed) navigate(navigateTo);
    }//Swal navigate on confirm.

    const handleRequestNewLink = async () => {
        const email = searchParams.get('email');

        try {
            const response = await axios.post(import.meta.env.VITE_API_VERIFY_EMAIL_REQUEST, {
                email: email
            });

            const responseMessage = response.data.message;

            swalNavigateOnConfirm({
                icon: "success",
                title: "Verification Link Expired",
                text: responseMessage,
                confirmButtonText: 'Go to Mock Mail',
                navigateTo: `/mock-mail?email=${email}`
            });
        } catch (error: any) {
            const errorMessage = getCatchMessage(error);

            if (error.response) {
                const statusCode = error.response.status;

                if (statusCode === 404 || statusCode === 409) {
                    swalNavigateOnConfirm({
                        icon: "error",
                        title: "ERROR",
                        text: errorMessage,
                        confirmButtonText: 'Return to Home',
                        navigateTo: `/`
                    });
                }
            } else {
                toast.error(errorMessage);
            }//ifelse
        }//trycatch
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
                            onClick={handleRequestNewLink}
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
                                Welcome to the <strong>User Mangement System</strong> project.
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

export default EmailVerfied;