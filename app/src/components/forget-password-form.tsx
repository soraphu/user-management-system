import { getCatchMessage } from "@/handler/request_handler"
import { useState } from "react"
import axios from "axios"
import { consoleLogOnDev } from "@/handler/log"

import { Link } from "react-router-dom"
import { Button } from "@/components/ui/button"
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { GoToMockMailButton } from "@/components/to-mock-mail-button"
import { toast } from "sonner"
import { API_FORGET_PASSWORD } from "@/handler/config"

const ForgetPasswordForm = () => {
    const [sentSuccess, setSentSuccess] = useState(false);
    const [email, setEmail] = useState("");

    const handleSendResetLink = async (e: any) => {
        e.preventDefault();
        const email = e.target.email.value;
        consoleLogOnDev(`Target email: ${email}`);

        try {
            const response = await axios.post(API_FORGET_PASSWORD, { email: email });
            const responseMessage = response.data.message;

            setSentSuccess(true);
            setEmail(email);
            toast.success(responseMessage);
        } catch (error) {
            const errorMessage = getCatchMessage(error);
            toast.error(errorMessage);
        }//try-catch.
    }; // End of handleSendResetLink.

    return (
        <Card className="w-full max-w-md shadow-lg">
            <CardHeader className="space-y-1">
                <CardTitle className="text-2xl font-bold tracking-tight">
                    Forgot password?
                </CardTitle>
                <CardDescription>
                    Enter your email address and we'll send you a link to reset your password.
                </CardDescription>
            </CardHeader>
            <CardContent >
                <form className="grid gap-4" onSubmit={handleSendResetLink} >
                    <div className="grid gap-2">
                        <Label htmlFor="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            placeholder="name@example.com"
                            required
                        />
                    </div>
                    <Button className="w-full" type="submit">
                        Send Reset Link
                    </Button>
                </form>
                {
                    sentSuccess ?
                        <>
                            <hr className="mt-4 mb-4" />
                            <GoToMockMailButton email={email} />
                        </>
                        :
                        <></>
                }
            </CardContent>
            <CardFooter className="flex flex-col gap-4">
                <div className="text-sm text-muted-foreground text-center">
                    Remembered your password?{" "}
                    <Link
                        to="/"
                        className="font-medium text-primary underline-offset-4 hover:underline"
                    >
                        Back to login
                    </Link>
                </div>
            </CardFooter>
        </Card>
    )
}

export default ForgetPasswordForm