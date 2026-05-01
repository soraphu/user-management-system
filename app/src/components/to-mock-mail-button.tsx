import { Link } from "react-router-dom"
import { Button } from "./ui/button"
import { Mail } from "lucide-react"

export const GoToMockMailButton = ({ email }: { email?: string | null }) => {

    return (
        <Link to={`/mockmail?email=${email || ""}`} target='_blank' >
            <Button
                type="button"
                variant="default"
                className="w-full bg-green-600"
            >
                Go to Mock Mail
                <Mail className="ml-2 h-4 w-4" />
            </Button>
        </Link>
    )
}
