import { consoleErrorOnDev } from "./log";

export function getCatchMessage(err: any): string {
    if (err.response && err.response.data) {
        const serverMessage = err.response.data.message;

        consoleErrorOnDev(err.response);
        consoleErrorOnDev(serverMessage);

        return serverMessage;
    } else {
        consoleErrorOnDev("Error message: " + err.message);

        return "Unable to connect to the server. Please try again later.";
    }
}//getCatchMessage