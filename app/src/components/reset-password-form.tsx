import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { KeyRound } from "lucide-react";
import { consoleLogOnDev } from "@/helper/log";
import { InputPasswordWithVisibleControl } from "./ui/password-visible-control";
import { toast } from "sonner";
import { getCatchMessage } from "@/helper/request_handler";
import axios from "axios";
import { useNavigate, useSearchParams } from "react-router-dom";
import Swal from "sweetalert2";
import { API_ENDPOINTS } from "@/helper/config";

const ResetPasswordForm = () => {
    const [searchParams] = useSearchParams();
    const token = searchParams.get("token");
    const navigate = useNavigate();

    const handleResetPassword = async () => {
        const passwordElement = document.getElementById("password") as HTMLInputElement;
        const password = passwordElement.value;
        consoleLogOnDev(password);

        if (!password) {
            toast.error("Password is required.");
            return; ``
        } //!Empty password.

        if (!token) {
            toast.error("Invalid reset token.");
            return;
        }//!Invalid token.

        if (password.length < 8) {
            toast.error("Password must be at least 8 characters long.");
            return;
        }//!Password characters long < 8.

        try {
            const reponse = await axios.post(API_ENDPOINTS.ResetPassword, {
                token: token,
                new_password: password
            });

            const responseMessage = reponse.data.message;

            //Created successfully.
            await Swal.fire({
                icon: "success",
                title: "SUCCESS",
                text: responseMessage,
                showConfirmButton: false,
                timer: 1500
            });

            //Route to login page.
            navigate(`/`);
        } catch (error) {
            const errorMessage = getCatchMessage(error);
            toast.error(errorMessage);
        }

    }//handle reset password logic here.

    return (
        <Card className="w-full max-w-md shadow-lg">
            <CardHeader className="space-y-1 text-center">
                <div className="flex justify-center mb-2">
                    <div className="rounded-full bg-primary/10 p-3">
                        <KeyRound className="h-6 w-6 text-primary" />
                    </div>
                </div>
                <CardTitle className="text-2xl font-bold tracking-tight">
                    Reset Password
                </CardTitle>
                <CardDescription>
                    Enter your new password below to secure your account.
                </CardDescription>
            </CardHeader>


            <CardContent className="grid gap-4">
                <div className="grid gap-2" >
                    <Label htmlFor="password">New Password</Label>
                    <InputPasswordWithVisibleControl id="password" />
                    <p className="text-xs text-muted-foreground">
                        Must be at least 8 characters long.
                    </p>
                </div>
            </CardContent>

            <CardFooter className="flex flex-col gap-3">
                <Button className="w-full" onClick={handleResetPassword}>
                    Reset Password
                </Button>
                <Button variant="link" className="text-sm text-muted-foreground" asChild>
                    <a href="/login">Back to login</a>
                </Button>
            </CardFooter>
        </Card>
    )
}

export default ResetPasswordForm