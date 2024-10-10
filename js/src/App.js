import React, { useState, useEffect } from 'react'
import { WooCropper } from './Components/WooCropper'
import { WooSingleCropper } from './Components/WooSingleCropper'
import { WooTextEditor } from './Components/WooTextEditor'
import './App.css'

export const App = () => {
	const images = document.querySelectorAll('#dws-woo-cropper-markup .dws_image')
	const texts = document.querySelectorAll('#dws-woo-cropper-markup .dws_text')

	const jQuery = window.jQuery
	const ajax_object = window.ajax_object

	const [croppedImages, setCropedImages] = useState({})
	const [editedTexts, setEditedTexts] = useState({})

	const [readyToAddToCart, setReadyAddToCart] = useState([false])

	const [singleImage, setSingleImage] = useState('')
	const [aspectRatio, setAspectRatio] = useState(
		parseFloat(ajax_object.aspect_ratios[document.querySelector('.variation_id')?.value]),
	)

	const add_to_cart_button = document.querySelectorAll('.single_add_to_cart_button')[0]
	const new_add_to_cart_button = add_to_cart_button.cloneNode(true)
	add_to_cart_button.parentNode.replaceChild(new_add_to_cart_button, add_to_cart_button)

	useEffect(() => {
		if (Object.keys(ajax_object.aspect_ratios).length > 0) {
			setReadyAddToCart([false])
		} else {
			setReadyAddToCart(Array.from(images).map(() => false))
		}
	}, [])

	useEffect(() => {
		var enableButton = true
		if (Array.isArray(readyToAddToCart)) {
			readyToAddToCart.forEach((e) => {
				if (!e) {
					enableButton = false
					return
				}
			})
		} else {
			enableButton = false
		}
		if (enableButton) {
			new_add_to_cart_button.disabled = false
		} else {
			new_add_to_cart_button.disabled = true
		}
	}, [readyToAddToCart])

	useEffect(() => {
		return () => {
			if (singleImage && singleImage.src) {
				URL.revokeObjectURL(singleImage.src)
			}
			let ratio = ajax_object.aspect_ratios[document.querySelector('.variation_id')?.value]
			setAspectRatio(parseFloat(ratio))
		}
	}, [singleImage])

	try {
		new_add_to_cart_button.addEventListener('click', function (e) {
			e.preventDefault()
			addToCartWithImages()
		})
	} catch (error) {
		console.error(error)
	}

	try {
		document.querySelector('.variations_form select').addEventListener('change', () => {
			setTimeout(() => {
				let ratio = ajax_object.aspect_ratios[document.querySelector('.variation_id')?.value]
				setAspectRatio(parseFloat(ratio))
			}, 300)
		})
	} catch (error) {
		console.error(error)
	}

	try {
		document.querySelector('.ux-swatches').addEventListener('click', () => {
			setTimeout(() => {
				let ratio = ajax_object.aspect_ratios[document.querySelector('.variation_id')?.value]
				setAspectRatio(parseFloat(ratio))
			}, 300)
		})
	} catch (error) {
		console.error(error)
	}

	const updateImagesAtIndex = (key, newValue, i) => {
		const newArray = { ...croppedImages }
		newArray[key] = newValue
		setCropedImages(newArray)

		const readyArray = [...readyToAddToCart]
		readyArray[i] = true
		setReadyAddToCart(readyArray)
	}

	const updateTextsAtIndex = (key, newValue) => {
		const newArray = { ...editedTexts }
		newArray[key] = newValue
		setEditedTexts(newArray)
	}

	const createFormData = () => {
		const formData = new FormData()
		if (Object.keys(ajax_object.aspect_ratios).length > 0) {
			formData.append('action', 'add_to_cart_with_single_image')
			formData.append('product_id', ajax_object.product_id)
			formData.append('variation_id', document.querySelector('.variation_id')?.value)
			formData.append('quantity', document.querySelector('.qty').value)
			Object.entries(croppedImages).forEach(([key, value]) => {
				formData.append(key, value)
			})
		} else {
			formData.append('action', 'add_to_cart_with_images')
			formData.append('product_id', ajax_object.product_id)
			formData.append('variation_id', document.querySelector('.variation_id')?.value)
			formData.append('dws_nonce', ajax_object.dws_nonce)
			formData.append('quantity', document.querySelector('.qty').value)
			Object.entries(croppedImages).forEach(([key, value]) => {
				formData.append(key, value)
			})

			Object.entries(editedTexts).forEach(([key, value]) => {
				formData.append(key, value)
			})
		}
		return formData
	}

	function addToCartWithImages() {
		jQuery('.single_add_to_cart_button').text('Kérlek várj, amíg a képek feltöltődnek!')
		jQuery.ajax({
			type: 'POST',
			url: ajax_object.ajax_url,
			data: createFormData(),
			processData: false,
			contentType: false,
			success: function (response) {
				//jQuery('.single_add_to_cart_button').text('Kosárba rakva!')
				window.location.href = '/kosar'
			},
			error: function (error) {
				jQuery('.single_add_to_cart_button').text('Hiba!')
			},
		})
	}

	if (Object.keys(ajax_object.aspect_ratios).length > 0) {
		return (
			<WooSingleCropper
				key={aspectRatio}
				image={singleImage}
				setImage={setSingleImage}
				aspectRatio={aspectRatio}
				setCroppedImage={(htmlID, image) => updateImagesAtIndex(htmlID, image, 0)}
			/>
		)
	} else {
		return (
			<>
				{Array.from(images).map((e, i) => {
					return (
						<WooCropper
							key={i}
							element={e}
							index={i}
							setCroppedImage={(htmlID, image) => updateImagesAtIndex(htmlID, image, i)}
						/>
					)
				})}
				{Array.from(texts).map((e, i) => {
					return (
						<WooTextEditor
							key={i}
							element={e}
							setEditedText={(htmlID, text) => updateTextsAtIndex(htmlID, text)}
						/>
					)
				})}
				{/* <button onClick={() => addToCartWithImages()}>Kosárba teszem</button> */}
			</>
		)
	}
}
