import React, { StrictMode } from 'react'
import ReactDOM from 'react-dom/client'
import { App } from './App'

function waitForElm(selector) {
	return new Promise((resolve) => {
		if (document.querySelector(selector)) {
			return resolve(document.querySelector(selector))
		}

		const observer = new MutationObserver((mutations) => {
			if (document.querySelector(selector)) {
				observer.disconnect()
				resolve(document.querySelector(selector))
			}
		})

		observer.observe(document.body, {
			childList: true,
			subtree: true,
		})
	})
}

waitForElm('#dws-woo-cropper').then((elm) => {
	const cropper_root = ReactDOM.createRoot(document.getElementById('dws-woo-cropper'))
	cropper_root.render(<App />)
})
