import { consoleErrorOnDev } from "./log";

export function getCatchMessage(err: any): string {
    if (err.response && err.response.data) {
        const serverMessage = err.response.data.message || "There is no message response from the server.";

        consoleErrorOnDev("===========================================");
        consoleErrorOnDev("Server response : Yes");
        consoleErrorOnDev(err.response);
        consoleErrorOnDev("API Server message: " + serverMessage);
        consoleErrorOnDev("===========================================");

        return serverMessage;
    } else {
        consoleErrorOnDev("===========================================");
        consoleErrorOnDev("Server response : No");
        consoleErrorOnDev("Error message: " + err.message);
        consoleErrorOnDev("===========================================");

        return "Unable to connect to the server. Please try again later.";
    }
}//getCatchMessage