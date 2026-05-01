import axios from 'axios';
import { useLocation, useNavigate, useSearchParams } from 'react-router-dom';

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
import { getCatchMessage } from '@/handler/request_handler';
import Swal from 'sweetalert2';
import { swalConfirmButtonColor } from '@/handler/config';

const SendVerifyEmail = () => {
    const [isResending, setIsResending] = useState(false);
    const [searchParams] = useSearchParams();
    const location = useLocation();
    const navigate = useNavigate();
    const email = searchParams.get("email");

    const requestEmailFields = async (text: string) => {
        const result = await Swal.fire({
            icon: "error",
            title: "Error",
            text: text,
            input: "email",
            inputPlaceholder: "Enter your email address",
            confirmButtonColor: swalConfirmButtonColor,
            confirmButtonText: "Enter",
        });

        if (result.isConfirmed) {
            navigate(`${location.pathname}?email=${result.value}`);
        }
    }//Handle required email on missing.

    const handleResendVerifyEmailLink = async () => {
        setIsResending(true);

        //Ensure email is available.
        if (!email) {
            setIsResending(false);
            requestEmailFields("The email is missing, Please provide your email and try again.");
            return;
        }//if

        try {
            // Send the email in the body to your verify email request API.
            const response = await axios.post(import.meta.env.VITE_API_VERIFY_EMAIL_REQUEST, { email: email });

            // Trigger a success.
            toast.success(response.data.message);
        } catch (error: any) {
            const errorMessage = getCatchMessage(error);

            if (error.response) {
                const statusCode = error.response.status;
                if (statusCode === 404) {
                    requestEmailFields(errorMessage);
                }
                if (statusCode === 409) {
                    const dialog = await Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: errorMessage,
                        confirmButtonColor: swalConfirmButtonColor,
                        confirmButtonText: "Go to login"
                    });
                    if (dialog.isConfirmed) {
                        navigate("/");
                    }//Navigate to login on confirm.
                }//Email already verified.
            }
            toast.error(errorMessage);

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
                        onClick={handleResendVerifyEmailLink}
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
                        Send verification email test
                    </p>
                </CardFooter>
            </Card>
        </div>
    );
}

export default SendVerifyEmail
