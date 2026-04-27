import { useState } from "react";
import { cn } from "@/lib/utils"; // shadcn helper
import { Button } from "@/components/ui/button";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Separator } from "@/components/ui/separator";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

interface MailItem {
    id: number;
    sender: string;
    subject: string;
    preview: string;
    time: string;
    isRead: boolean;
}

const MOCK_EMAILS: MailItem[] = [
    { id: 1, sender: "RMUTL Registrar", subject: "Internship Approval", preview: "Your internship for the 2026 term has been approved...", time: "10:30 AM", isRead: false },
    { id: 2, sender: "GitHub", subject: "Successful Deployment", preview: "Your IoT Dashboard is now live on GitHub Pages...", time: "Yesterday", isRead: false },
    { id: 3, sender: "Supabase", subject: "Database Usage Alert", preview: "Your project has reached 50% storage...", time: "Monday", isRead: false },
];

export default function MockMail() {
    const [selectedId, setSelectedId] = useState<number | null>(null);
    const selectedEmail = MOCK_EMAILS.find(e => e.id === selectedId);

    return (
        <div className="flex h-screen bg-background text-foreground">
            {/* 1. Sidebar List with ScrollArea */}
            <div className="w-[400px] flex flex-col border-r">
                <div className="p-4 font-bold text-xl flex justify-between items-center">
                    Inbox
                    <Badge variant="secondary">{MOCK_EMAILS.filter(e => !e.isRead).length}</Badge>
                </div>
                <Separator />

                <ScrollArea className="flex-1">
                    <div className="flex flex-col gap-2 p-4">
                        {MOCK_EMAILS.map((email) => (
                            <button
                                key={email.id}
                                onClick={() => {
                                    if (!email.isRead) email.isRead = true;
                                    setSelectedId(email.id)
                                }}
                                className={cn(
                                    "flex flex-col items-start gap-2 rounded-lg border p-3 text-left text-sm transition-all hover:bg-accent",
                                    selectedId === email.id && "bg-muted"
                                )}
                            >
                                <div className="flex w-full flex-col gap-1">
                                    <div className="flex items-center">
                                        <div className="flex items-center gap-2">
                                            <div className="font-semibold">{email.sender}</div>
                                            {!email.isRead && (
                                                <span className="flex h-2 w-2 rounded-full bg-blue-600" />
                                            )}
                                        </div>
                                        <div className={cn("ml-auto text-xs text-muted-foreground")}>
                                            {email.time}
                                        </div>
                                    </div>
                                    <div className="text-xs font-medium">{email.subject}</div>
                                </div>
                                <div className="line-clamp-2 text-xs text-muted-foreground">
                                    {email.preview}
                                </div>
                            </button>
                        ))}
                    </div>
                </ScrollArea>
            </div>

            {/* 2. Content Detail View */}
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
                            <CardContent className="p-6 text-sm">
                                {selectedEmail.preview}
                                <p className="mt-4 text-muted-foreground italic">
                                    [Full message content would be rendered here via API]
                                </p>
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
    );
}