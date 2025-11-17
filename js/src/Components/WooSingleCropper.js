import React, { useState, useRef } from 'react'
import { Cropper } from 'react-advanced-cropper'
import 'react-advanced-cropper/dist/style.css'

export const WooSingleCropper = ({ aspectRatio, image, setImage, setCroppedImage, skipCropper = false }) => {
	const cropperRef = useRef(null)
	const inputRef = useRef(null)

	const [imageSize, setImageSize] = useState({ width: 1, height: 1 })
	const [coordinates, setCoordinates] = useState({ t: 0, l: 0, x: 0, y: 0 })
	const [previewImage, setPreviewImage] = useState('')
	const [done, setDone] = useState(false)

	const preview = (e) => {
		e.preventDefault()

		if (skipCropper) {
			if (image?.file && image?.src) {
				setCroppedImage(0, image.file)
				setPreviewImage(image.src)
				setDone(true)
			}
			return
		}

		const canvas = cropperRef.current?.getCanvas()
		if (canvas) {
			canvas.toBlob(
				(blob) => {
					if (blob) {
						setCroppedImage(0, blob)
						const blobUrl = URL.createObjectURL(blob)
						setPreviewImage(blobUrl)
					}
				},
				'image/' + window.ajax_object.filetype,
				parseFloat(window.ajax_object.quality / 100),
			)
		}
		setDone(true)
	}

	const onLoadImage = (event) => {
		const { files } = event.target

		if (files && files[0]) {
			const file = files[0]
			const blob = URL.createObjectURL(file)
			setImage({
				src: blob,
				type: file.type,
				file,
			})
			if (skipCropper) {
				setPreviewImage(blob)
				setCroppedImage(0, file)
				setDone(true)
			} else {
				setPreviewImage('')
				setDone(false)
			}
		}
		event.target.value = ''
		if (!skipCropper) {
			setPreviewImage('')
		}
	}

	const defaultSize = ({ imageSize, visibleArea }) => {
		setImageSize(imageSize)
		return {
			width: (visibleArea || imageSize).width,
			height: (visibleArea || imageSize).height,
		}
	}

	return (
		<>
			<div className='dws-wrapper dws-item' style={done ? { backgroundColor: 'rgb(93 195 34 / 13%)', border: '1px solid #0000008c' } : { backgroundColor: '#f4f4f4' }}>
				{!skipCropper && <div className='dws-title'>{done ? 'Feltöltve! Látványterv a kész képről, amit kézhez kapsz!' : 'Kép feltöltése'}</div>}
				{skipCropper && done && (
					<div className='dws-title'>
						Alább láthatod a feltöltött képet.
						<br />A rendelés után grafikusunk méretre vágja és beállítja, hogy tökéletesen illeszkedjen a választott mérethez.
					</div>
				)}

				<div className='dws-content-active'>
					<div style={{ position: 'relative', width: '100%' }}>
						{previewImage !== '' && done && (
							<img
								src={skipCropper ? image.src : previewImage}
								alt='' // decorative preview
								style={{ width: '100%', borderRadius: 10, marginBottom: 20, marginTop: 10 }}
								className={`dws-preview${skipCropper ? '-thumbnail' : ''}`}
							></img>
						)}

						{!done && !skipCropper && (
							<Cropper
								ref={cropperRef}
								stencilProps={{
									aspectRatio: aspectRatio,
								}}
								defaultSize={defaultSize}
								src={image && image.src}
								className={'cropper'}
								style={{ marginBottom: 10, width: '100%' }}
							/>
						)}

						<input ref={inputRef} style={{ display: 'none' }} type='file' accept='image/*' onChange={onLoadImage} />

						<button
							className={`dws-input${skipCropper ? ' no-margin' : ''}`}
							onClick={(e) => {
								e.preventDefault()
								inputRef.current.click()
								setDone(false)
								setPreviewImage('')
							}}
						>
							{image && image.src ? 'Kép cseréje' : 'Kép választása'}
						</button>

						{image && image.src && !done && !skipCropper && (
							<button style={{ width: '100%', marginTop: '10px' }} className='dws-input' onClick={preview}>
								Kész
							</button>
						)}

						{!skipCropper && image && image.src && done && (
							<button
								style={{ width: '100%', marginTop: '10px' }}
								className='dws-input'
								onClick={(e) => {
									e.preventDefault()
									setDone(false)
								}}
							>
								Látványterv módosítása
							</button>
						)}
					</div>
				</div>
			</div>
		</>
	)
}
