import React, { useEffect, useState } from 'react'
import 'react-advanced-cropper/dist/style.css'

export const WooTextEditor = ({ element, setEditedText }) => {
	const [value, setValue] = useState('')

	useEffect(() => {
		setValue(element.textContent.trim())
	}, [])

	const handleChange = (event) => {
		setValue(event.target.value)
		setEditedText(element.id, event.target.value)
		element.textContent = event.target.value
	}

	return (
		<>
			<input
				style={{
					width: '100%',
					height: '26px',
					border: '1px solid rgba(195, 34, 48, 1)',
					marginBottom: 10,
					borderRadius: 10,
					padding: '4px 10px',
				}}
				type='text'
				value={value}
				onChange={handleChange}
			/>
			<br />
		</>
	)
}
