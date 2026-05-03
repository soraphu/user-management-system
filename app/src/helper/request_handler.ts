import { consoleErrorDevMode } from "./log";
import { useAuth } from "@/auth/AuthContext";
// import { API_ACTION, useCookie } from "./config";

// const { accessToken, setAccessToken } = useAuth();

export function getCatchMessage(err: any): string {
    if (err.response && err.response.data) {
        const serverMessage = err.response.data.message || "There is no message response from the server.";

        consoleErrorDevMode("===========================================");
        consoleErrorDevMode("Server response : Yes");
        consoleErrorDevMode(err.response);
        consoleErrorDevMode("API Server message: " + serverMessage);
        consoleErrorDevMode("===========================================");

        return serverMessage;
    } else {
        consoleErrorDevMode("===========================================");
        consoleErrorDevMode("Server response : No");
        consoleErrorDevMode("Error message: " + err.message);
        consoleErrorDevMode("===========================================");

        return "Unable to connect to the server. Please try again later.";
    }
}//getCatchMessage

// export async function handleRequestAcessAction({ action, returnData }: { action: Function, returnData: boolean }) {

//     if (!accessToken) {
//         handleRefreshAccessToken(() => useCookie.post(API_ACTION.FetchUser, { accessToken: accessToken }));
//     }
// }

// async function handleRefreshAccessToken(requestAction: Function) {
//     try {
//         const response = requestAction();

//         setAccessToken(response.data.token);
//     } catch (error) {

//     }
// }