import { isDevMode } from "./config";

export function consoleLogOnDev(message: any) {
    if (isDevMode) {
        console.log(message);
    }
} //Log on dev mode only.

export function consoleErrorOnDev(message: any) {
    if (isDevMode) {
        console.error(message);
    }
} //Log on dev mode only.