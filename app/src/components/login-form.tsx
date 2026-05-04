import { cn } from "@/lib/utils"
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/auth/AuthContext";

//Import components.
import { Button } from "@/components/ui/button"
import {
  Field,
  FieldDescription,
  FieldGroup,
  FieldLabel,
} from "@/components/ui/field"
import { Input } from "@/components/ui/input"
import { InputPasswordWithVisibleControl } from "./ui/password-visible-control"
import { toast } from 'sonner';
import { API_AUTH, reqWithCookie } from "@/helper/config";
import { consoleLogDevMode } from "@/helper/log";
import { getCatchMessage } from "@/helper/request_handler";

export function LoginForm({ className, ...props }: React.ComponentProps<"form">) {
  const { setAccessToken } = useAuth();
  const navigate = useNavigate();

  const handleUserLogin = async (e: any) => {
    e.preventDefault();
    interface UserItem {
      email: string;
      password: string;
    }; //UserItem type

    const user: UserItem = {
      email: e.target.email.value,
      password: e.target.password.value
    };

    try {
      const response = await reqWithCookie.post(API_AUTH.Login, user);

      //Login success.
      consoleLogDevMode(response.data);
      const newAccessToken = response.data.access_token;
      setAccessToken(newAccessToken);

      toast.success("Login successfully.");

      consoleLogDevMode(document.cookie);

      navigate("/home");
    } catch (error: any) {
      const errorMessage = getCatchMessage(error);

      if (error.response && error.response.status === 401) {
        navigate(`/verify-email-request?email=${user.email}`);
        toast.error(errorMessage);
        return;
      }

      toast.error(errorMessage);
    } //trycatch
  }; //Handle user login.

  return (
    <form className={cn("flex flex-col gap-6", className)} {...props} onSubmit={handleUserLogin}>
      <FieldGroup>
        <div className="flex flex-col items-center gap-1 text-center">
          <h1 className="text-2xl font-bold">Login to your account</h1>
          <p className="text-sm text-balance text-muted-foreground">
            Enter your email below to login to your account
          </p>
        </div>
        <Field>
          <FieldLabel htmlFor="email">Email</FieldLabel>
          <Input
            id="email"
            type="email"
            placeholder="m@example.com"
            required
            className="bg-background"
          />
        </Field>
        <Field>
          <div className="flex items-center">
            <FieldLabel htmlFor="password">Password</FieldLabel>
            <a
              href="/password/forget"
              className="ml-auto text-sm underline-offset-4 hover:underline"
            >
              Forgot your password?
            </a>
          </div>
          <InputPasswordWithVisibleControl id="password" />
        </Field>
        <Field>
          <Button type="submit">Login</Button>
        </Field>
        <Field>
          <FieldDescription className="text-center">
            Don&apos;t have an account?{" "}
            <a href="/register" className="underline underline-offset-4">
              Sign up
            </a>
          </FieldDescription>
        </Field>
      </FieldGroup>
    </form>
  ) //return HTML.
}//Login form components.

export default LoginForm