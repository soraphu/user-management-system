import { consoleErrorDevMode } from "./log";

export function getCatchMessage(err: any): string {
    if (err.response && err.response.data) {
        const serverMessage = err.response.data.message || "There is no message response from the server.";

        consoleErrorDevMode("===========================================");
        consoleErrorDevMode("{ Server response } == Yes");
        consoleErrorDevMode("{ Status Code } == " + err.status);
        consoleErrorDevMode("{ API Server message } == " + serverMessage);
        consoleErrorDevMode("{ Entire response } == ");
        consoleErrorDevMode(err.response);
        consoleErrorDevMode("===========================================");

        return serverMessage;
    } else {
        consoleErrorDevMode("===========================================");
        consoleErrorDevMode("{ Server response } == No");
        consoleErrorDevMode("{ Error message }== " + err.message);
        consoleErrorDevMode("===========================================");

        return "Unable to connect to the server. Please try again later.";
    }
}//getCatchMessage