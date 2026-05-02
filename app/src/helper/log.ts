import { isDevMode } from "./config";

export function consoleLogDevMode(message: any) {
    if (isDevMode) {
        console.log(message);
    }
} //Log on dev mode only.

export function consoleErrorDevMode(message: any) {
    if (isDevMode) {
        console.error(message);
    }
} //Log on dev mode only.