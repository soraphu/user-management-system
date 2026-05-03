import { useAuth } from "@/auth/AuthContext";
import { getCatchMessage } from "./request_handler";
import { API_ACTION, reqWithCookie } from "./config";
import { API_AUTH } from "./config";
import { consoleLogDevMode } from "./log";
import type { Method } from "axios";

export const useUserAction = () => {
    const { setAccessToken, accessToken, handleLogout, setUser } = useAuth();


    const fetchUser = async () => {
        const response = await handleReqAccessAction({ method: "GET", url: API_ACTION.FetchUser, accessToken });
        const user = response!.data.user;
        setUser(user);
    }

    const handleReqAccessAction = async ({ method, url, body, accessToken }: { method: Method, url: string, body?: object, accessToken: string | null }) => {
        reqWithCookie.defaults.headers.common['Authorization'] = `Bearer ${accessToken}`;

        while (true) {
            if (!accessToken) {
                consoleLogDevMode("No access token. Attemping refresh");
                try {
                    const response = await reqWithCookie.get(API_AUTH.RefreshToken);
                    const newAccessToken = response.data.access_token;

                    consoleLogDevMode(response.data);
                    consoleLogDevMode("Receive new access token");
                    setAccessToken(newAccessToken);

                    accessToken = newAccessToken;
                } catch (error) {
                    getCatchMessage(error);
                    consoleLogDevMode("Log out");
                    handleLogout();
                    return null;
                }
            }

            try {
                const response = await reqWithCookie({
                    method: method,
                    url: url,
                    data: body,
                    headers: { Authorization: `Bearer ${accessToken}` }
                });

                consoleLogDevMode(response);
                return response;
            } catch (error) {
                getCatchMessage(error);
                setAccessToken(null);
            }
        }//while
    } //handleFetchUser.

    return { handleReqAccessAction, fetchUser };
}

