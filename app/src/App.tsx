import React from 'react'
import { createBrowserRouter, RouterProvider } from 'react-router-dom'

import Home from './pages/Home';
import Login from './pages/Login';
import Register from './pages/Register';
import Mockmail from './pages/Mockmail';
import Reset_password from './pages/Reset_password';
import Forget_password from './pages/Forget_password';

const router = createBrowserRouter([
  { path: '/', element: <Home /> },
  { path: '/login', element: <Login /> },
  { path: '/register', element: <Register /> },
  { path: '/mockmail', element: <Mockmail /> },
  { path: '/password/reset', element: <Reset_password /> },
  { path: '/password/forget', element: <Forget_password /> },
]);

const App = () => {
  return (
    <div>
      <RouterProvider router={router} />
    </div>
  )
}

export default App