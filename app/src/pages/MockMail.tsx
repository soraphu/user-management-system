import Swal from "sweetalert2";
import { useSearchParams } from "react-router-dom";
import { useNavigate } from "react-router-dom";
import axios from "axios";

import { useState, type JSX, type ReactNode, useEffect } from "react";
import { cn } from "@/lib/utils"; // shadcn helper
import { Button } from "@/components/ui/button";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Separator } from "@/components/ui/separator";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { NavigateButton } from "@/components/ui/link-button";
import { Navbar } from "@/components/ui/navbar";

interface MailItem {
    id: number;
    sender: string;
    subject: string;
    preview: string;
    url?: string;
    buttonLabel?: string;
    time: string;
    isRead: boolean;
}

export default function MockMail() {
    const [selectedId, setSelectedId] = useState<number | null>(null);
    const [unReadAmount, setUnReadAmount] = useState<number>(0);
    const [searchParams] = useSearchParams();
    const [inbox, setInbox] = useState<MailItem[]>([]);
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();

    const selectedEmail = inbox.find(e => e.id === selectedId);
    const email = searchParams.get("email");

    useEffect(() => {
        if (!email) {
            navigate("/");
            return;
        } //If email is empty, exit.

        handleFetchInbox();//Fetch

        setUnReadAmount(inbox.filter(e => !e.isRead).length);
        Swal.fire({
            title: '<strong>Mock Mail System</strong>',
            icon: 'info',
            html: `
            <div class="text-left space-y-3 text-gray-700">
            <p>This is a <b>shared sandbox</b> environment for system testing, everyone can access this inbox to test the system features.</p>
          <hr class="my-2" />
          <p class="text-sm italic text-blue-600">
            <strong>Note:</strong> In a production environment, this would be your 
            <b>private, encrypted mailbox</b> accessible only through your verified account.
          </p>
        </div>
      `,
            confirmButtonText: 'I Understand',
            confirmButtonColor: '#2563eb', // Tailwind blue-600
            background: '#ffffff',
        });
    }, [navigate]);

    const handleFetchInbox = async () => {
        try {
            setLoading(true);

            //GET
            const dataResponse = await axios.get(import.meta.env.VITE_API_GET_INBOX + `?email=${email}`);
            const newInbox: MailItem[] = dataResponse.data;
            // If 200 OK, save the data
            setInbox(newInbox);
            setUnReadAmount(newInbox.filter(e => !e.isRead).length);
        } catch (error: any) {
            const status = error.response?.status;

            let errorMsg = "An unexpected error occurred. Please try again.";
            if (status === 404) {
                errorMsg = "This email not found.";
            }

            const pressReturn = await Swal.fire({
                title: "System Error",
                text: errorMsg,
                icon: "error",
                confirmButtonText: "Return to Home",
                confirmButtonColor: "#2563eb",
                allowOutsideClick: false, // Force them to click the button
            });

            if (pressReturn.isConfirmed) navigate("/");
        } finally {
            setLoading(false);
        }
    }; //Hanle fetch inbox.

    return (
        <div className="flex flex-col h-screen bg-background text-foreground">
            <Navbar email={email!}></Navbar>
            <div className="flex flex-col-2 h-full" >
                {/* SideBar */}
                <div className="w-[400px] flex flex-col border-r">
                    <div className="p-4 font-bold text-xl flex justify-between items-center">
                        Inbox
                        <Badge variant="secondary">{unReadAmount}</Badge>
                    </div>
                    <Separator />

                    <ScrollArea className="flex-1">
                        <div className="flex flex-col gap-2 p-4">
                            {inbox.map((mail) => (
                                <button
                                    key={mail.id}
                                    onClick={() => {
                                        if (!mail.isRead) mail.isRead = true;

                                        setUnReadAmount(inbox.filter(e => !e.isRead).length);
                                        setSelectedId(mail.id);
                                    }}
                                    className={cn(
                                        "flex flex-col items-start gap-2 rounded-lg border p-3 text-left text-sm transition-all hover:bg-accent",
                                        selectedId === mail.id && "bg-muted"
                                    )}
                                >
                                    <div className="flex w-full flex-col gap-1">
                                        <div className="flex items-center">
                                            <div className="flex items-center gap-2">
                                                <div className="font-semibold">{mail.sender}</div>
                                                {!mail.isRead && (
                                                    <span className="flex h-2 w-2 rounded-full bg-blue-600" />
                                                )}
                                            </div>
                                            <div className={cn("ml-auto text-xs text-muted-foreground")}>
                                                {mail.time}
                                            </div>
                                        </div>
                                        <div className="text-xs font-medium">{mail.subject}</div>
                                    </div>
                                    <div className="line-clamp-2 text-xs text-muted-foreground">
                                        {mail.preview}
                                    </div>
                                </button>
                            ))}
                        </div>
                    </ScrollArea>
                </div>

                {/* Content */}
                <div className="flex-1 flex flex-col">
                    {selectedEmail ? (
                        <div className="p-6">
                            <Card>
                                <CardHeader className="flex flex-row items-start justify-between">
                                    <div className="grid gap-1">
                                        <CardTitle>{selectedEmail.subject}</CardTitle>
                                        <p className="text-sm text-muted-foreground">From: {selectedEmail.sender}</p>
                                    </div>
                                    <Button variant="outline" size="sm" onClick={() => setSelectedId(null)}>
                                        Close
                                    </Button>
                                </CardHeader>
                                <Separator />
                                <CardContent className="p-6 text-sm flex flex-col gap-y-6">
                                    {selectedEmail.preview}
                                    <NavigateButton
                                        className="bg-blue-600"
                                        hidden={selectedEmail.url ? false : true} to={selectedEmail.url ?? ""} label={selectedEmail.buttonLabel ?? ""}
                                    />
                                </CardContent>
                            </Card>
                        </div>
                    ) : (
                        <div className="flex-1 flex items-center justify-center text-muted-foreground italic">
                            Select a message to view content
                        </div>
                    )}
                </div>
            </div>

        </div >
    );
}//MockMail