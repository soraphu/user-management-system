import { useAuth } from "@/auth/AuthContext"

const DashboardPage = () => {
    const { accessToken } = useAuth();

    return (
        <div>{`Saved token is ${accessToken}.`}</div>
    )
}

export default DashboardPage