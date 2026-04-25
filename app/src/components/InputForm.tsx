import axios from "axios";
import React, { useEffect, type JSX } from "react";
import { Eye, EyeClosed } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import Swal from 'sweetalert2';

//Import Components.
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
} from "@/components/ui/field"
import { toast } from "sonner";

export const LoginForm = () => {
    return (
        <div>This is Login Form</div>
    )
}//Login Form.

export function RegisterForm({
    className,
    ...props
}: React.ComponentProps<"div">) {
    const [passwordVisible, setPasswordVisible] = React.useState(false);
    const navigate = useNavigate();
    const passwordVisibleControlButton: JSX.Element = <button
        type="button"
        onClick={() => setPasswordVisible(!passwordVisible)}
        className="absolute right-3 top-1/4 size-fit"
    >
        {passwordVisible ? (
            <EyeClosed className="h-4 w-4" />
        ) : (
            <Eye className="h-4 w-4" />
        )}
    </button>;

    async function handleRegister(e: any) {
        e.preventDefault();
        const user = {
            username: e.target.name.value,
            email: e.target.email.value,
            password: e.target.password.value,
        }//Set data.

        if (!handleSignupDataValidation(user)) return;

        try {
            await axios.post(import.meta.env.VITE_API_REGISTER, user);

            //Created successfully.
            await Swal.fire({
                icon: "success",
                title: "SUCCESS",
                text: "User registered successfully.",
                showConfirmButton: false,
                timer: 1500
            });
            navigate('/');

        } catch (error: any) {
            //Failed to create.
            if (error.status === 409) {
                toast.error("This email already exist.");
            } else {
                console.error(error);
                toast.error("Fail to create account. Please try again.");
            }//Handle on error.
        }//try-signup.
    }//Handle on signup.

    function handleSignupDataValidation(user: any) {
        const realDomains = ["@gmail.com", "@outlook.com", "@hotmail.com", "@yahoo.com"];

        // Check if the input ends with any of these
        const isRealEmail = realDomains.some(domain => user.email.toLowerCase().endsWith(domain));

        if (isRealEmail) {
            Swal.fire({
                icon: "error",
                title: "REAL DOMAIN DETECTED",
                text: "For PDPA safety, please use a fake email domain like @example.com, @test.com or each other."
            });

            return false;
        }//Fake email validation.
        if (user.password.length < 8) {
            toast.error("Password must be at least 8 characters long");

            return false;
        }//Password validation.

        //true = Verified | false = Not approved.
        return true;
    }//Handle data validation.

    return (
        <div className={cn("flex flex-col gap-6", className)} {...props}>
            <Card>
                <CardHeader className="text-center">
                    <CardTitle className="text-xl">Create your account</CardTitle>
                    <CardDescription >
                        Enter your fake info below to create account.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleRegister} >
                        <FieldGroup className="justify-self-center max-w-sm" >
                            <Field>
                                <FieldLabel htmlFor="name">Username</FieldLabel>
                                <Input id="name" type="text" placeholder="JohnDoe" required />
                            </Field>
                            <Field>
                                <FieldLabel htmlFor="email">Email</FieldLabel>
                                <Input
                                    id="email"
                                    type="email"
                                    placeholder="m@example.com"
                                    required
                                />
                            </Field>
                            <Field>
                                <Field>
                                    <Field className="relative w-full" >
                                        <FieldLabel htmlFor="password">Password</FieldLabel>
                                        <div className="relative" >
                                            <Input id="password" type={passwordVisible ? "text" : "password"} required />
                                            {passwordVisibleControlButton}
                                        </div>
                                    </Field>
                                </Field>
                                <FieldDescription>
                                    Must be at least 8 characters long.
                                </FieldDescription>
                            </Field>
                            <Field>
                                <Button type="submit" >Create Account</Button>
                                <FieldDescription className="text-center">
                                    Already have an account? <a href="/login">Sign in</a>
                                </FieldDescription>
                            </Field>
                        </FieldGroup>
                    </form>
                </CardContent>
            </Card>
            <FieldDescription className="px-6 text-center">
                By clicking continue, you agree to our <a href="#">Terms of Service</a>{" "}
                and <a href="#">Privacy Policy</a>.
            </FieldDescription>
        </div>
    )//return HTML;
}//Signup Form Components.