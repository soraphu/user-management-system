import { useState, type JSX } from 'react'

function App(): JSX.Element {
  const [name, setName] = useState('Soraphu');

  return (
    <>
      <h1>Welcome to My App {name} </h1>
      <button onClick={() => setName('Alice')}>Change Name</button>
    </>
  )
}

export default App
