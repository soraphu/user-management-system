import React from 'react'
import { createBrowserRouter, RouterProvider } from 'react-router-dom'

import Home from './pages/welcome';
import Login from './pages/login';

const router = createBrowserRouter([
  { path: '/', element: <Home /> },
  { path: '/login', element: <Login /> },
]);

const App = () => {
  return (
    <div>
      <RouterProvider router={router} />
    </div>
  )
}

export default App