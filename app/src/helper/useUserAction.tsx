import { useNavigate } from "react-router-dom";
import { useAuth } from "@/auth/AuthContext";
import { getCatchMessage } from "./request_handler";
import { API_ACTION, reqWithCookie } from "./config";
import { API_AUTH } from "./config";
import { consoleLogDevMode } from "./log";
import type { Method } from "axios";

export const useUserAction = () => {
    const { setAccessToken, accessToken, handleLogout, setUser, user } = useAuth();
    const navigate = useNavigate();

    const ensureLoggedIn = async () => {
        if (accessToken || user) {
            navigate("/home");
            return true;
        }

        try {
            const response = await reqWithCookie.get(API_AUTH.RefreshToken);
            const newAccessToken = response.data.access_token;
            if (newAccessToken) {
                setAccessToken(newAccessToken);
                navigate("/home");
                return true;
            }
        } catch (error) {
            getCatchMessage(error);
        }
    }//Redirect to home on logged in.

    const fetchUser = async () => {
        try {
            const response = await handleReqAccessAction({ method: "GET", url: API_ACTION.FetchUser });

            const user = response?.data.user;
            setUser(user);
        } catch (error) {
            getCatchMessage(error);
        }
    } //Fetch User.

    const handleReqAccessAction = async ({ method, url, body }: { method: Method, url: string, body?: object }) => {
        let recentToken: string | null = accessToken;

        const refreshAccessToken = async () => {
            consoleLogDevMode("No access token. Attempting refresh");

            try {
                const response = await reqWithCookie.get(API_AUTH.RefreshToken);
                const newAccessToken = response.data.access_token;

                consoleLogDevMode(response.data);
                consoleLogDevMode("Received new access token");
                setAccessToken(newAccessToken);

                // success.
                return newAccessToken as string;
            } catch (error) {
                getCatchMessage(error);
                handleLogout();
                return null;
            }
        };

        const executeRequest = async (newAccessToken: string) => {
            return await reqWithCookie({
                method,
                url,
                data: body,
                headers: { Authorization: `Bearer ${newAccessToken}` }
            });
        };

        if (!recentToken) {
            recentToken = await refreshAccessToken();
            if (!recentToken) return null;
        }

        try {
            const response = await executeRequest(recentToken);
            consoleLogDevMode(response);

            return response;
        } catch (error) {
            getCatchMessage(error);
            setAccessToken(null);

            const newAccessToken = await refreshAccessToken();
            if (!newAccessToken) return null;

            const response = await executeRequest(newAccessToken);
            consoleLogDevMode(response);
            return response;
        }
    } //handleReqAccessAction.

    return { handleReqAccessAction, fetchUser, ensureLoggedIn };
}

