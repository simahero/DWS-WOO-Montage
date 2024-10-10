import React, { useState } from 'react'

const Accordion = ({ items }) => {
	const [activeIndices, setActiveIndices] = useState([])

	const onTitleClick = (index) => {
		const currentIndex = activeIndices.indexOf(index)
		let newActiveIndices = [...activeIndices]

		if (currentIndex > -1) {
			newActiveIndices.splice(currentIndex, 1)
		} else {
			newActiveIndices.push(index)
		}

		setActiveIndices(newActiveIndices)
	}

	const isItemActive = (index) => {
		return activeIndices.includes(index)
	}

	const renderedItems = items.map((item, index) => {
		const active = isItemActive(index) ? 'active' : ''

		return (
			<div key={index} className='accordion-item'>
				<div className={`accordion-title ${active}`} onClick={() => onTitleClick(index)}>
					{item.title}
				</div>
				<div className={`accordion-content ${active}`}>{item.content}</div>
			</div>
		)
	})

	return <div className='accordion'>{renderedItems}</div>
}

export default Accordion
