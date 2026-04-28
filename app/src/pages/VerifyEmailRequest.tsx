import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useSearchParams } from 'react-router-dom';

import Swal from 'sweetalert2';
import { Link } from 'react-router-dom';
import { useState } from 'react';
import { Mail, ArrowRight, RefreshCw } from 'lucide-react';
import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { toast } from 'sonner';

const VerifyEmailRequest = () => {
    const [isResending, setIsResending] = useState(false);
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();

    const email = searchParams.get("email");

    const handleResend = async () => {
        setIsResending(true);
        // Mocking an API call to your PHP backend
        try {
            // 1. The POST Request
            // We send the email in the body to your PHP API
            await axios.post(import.meta.env.VITE_API_VERIFY_EMAIL_REQUEST, {
                email: email
            });

            // 2. SUCCESS (Status 2xx)

            // Trigger a success.
            toast.success("Verification link resent successfully!");

        } catch (error: any) {
            // 3. ERROR (Status 400, 401, 500, etc.)
            // Axios automatically jumps here if the status code is 4xx or 5xx.

            if (error.response) {
                // The server responded with a status code outside the 2xx range
                console.error("Server Error:", error.response.data);
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
            } else if (error.request) {
                // The request was made but no response was received (Server is down)
                console.error("Network Error: No response from server");
                toast.error("Cannot connect to the server.");

            } else {
                // Something happened in setting up the request that triggered an Error
                toast.error(`Request Error: ${error.message}`);
            }
        } finally {
            setIsResending(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-slate-50 p-4">
            <Card className="w-full max-w-md shadow-lg">
                <CardHeader className="space-y-1 flex flex-col items-center">
                    <div className="h-12 w-12 bg-primary/10 rounded-full flex items-center justify-center mb-4">
                        <Mail className="h-6 w-6 text-primary" />
                    </div>
                    <CardTitle className="text-2xl font-bold">Check your email</CardTitle>
                    <CardDescription className="text-center">
                        We've sent a verification link to your mock mail.
                        Please click the link to confirm your account.
                    </CardDescription>
                </CardHeader>

                <CardContent className="grid gap-4">
                    <Link to={`/mockmail?email=${email}`} target='_blank' >
                        <Button
                            variant="default"
                            className="w-full"
                        >
                            Go to Mock Mail
                            <ArrowRight className="ml-2 h-4 w-4" />
                        </Button>
                    </Link>

                    <Button
                        variant="outline"
                        className="w-full"
                        onClick={handleResend}
                        disabled={isResending}
                    >
                        {isResending ? (
                            <RefreshCw className="mr-2 h-4 w-4 animate-spin" />
                        ) : null}
                        {isResending ? "Sending..." : "Resend email"}
                    </Button>
                </CardContent>

                <CardFooter className="flex justify-center">
                    <p className="text-sm text-muted-foreground">
                        Verify email send test
                    </p>
                </CardFooter>
            </Card>
        </div>
    );
}

export default VerifyEmailRequest
