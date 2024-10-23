import React, { useState, useRef, useEffect } from 'react'
import { Cropper } from 'react-advanced-cropper'
import 'react-advanced-cropper/dist/style.css'

export const WooCropper = ({ element, index, setCroppedImage }) => {
	const cropperRef = useRef(null)
	const inputRef = useRef(null)

	const [imageSize, setImageSize] = useState({ width: 1, height: 1 })
	const [coordinates, setCoordinates] = useState({ t: 0, l: 0, x: 0, y: 0 })
	const [image, setImage] = useState('')
	const [done, setDone] = useState(false)

	const aspectRatio = element.getAttribute('width') / element.getAttribute('height')

	useEffect(() => {
		return () => {
			if (image && image.src) {
				URL.revokeObjectURL(image.src)
			}
		}
	}, [image])

	const preview = (e) => {
		e.preventDefault()
		const canvas = cropperRef.current?.getCanvas()
		if (canvas) {
			canvas.toBlob(
				(blob) => {
					if (blob) {
						setCroppedImage(0, blob)
						const blobUrl = URL.createObjectURL(blob)
						element.setAttribute('href', blobUrl)
					}
				},
				'image/webp',
				0.8,
			)
		}
		setDone(true)
	}

	const onLoadImage = (event) => {
		const { files } = event.target

		if (files && files[0]) {
			const blob = URL.createObjectURL(files[0])
			setImage({
				src: blob,
				type: files[0].type,
			})
		}
		event.target.value = ''
	}

	const onChange = (cropper) => {
		setCoordinates({
			w: cropper.getCoordinates().width,
			h: cropper.getCoordinates().height,
			l: cropper.getCoordinates().left,
			t: cropper.getCoordinates().top,
		})
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
			<div
				className='dws-wrapper dws-item'
				style={
					done
						? { backgroundColor: 'rgb(93 195 34 / 13%)', border: '1px solid #5dc322' }
						: { backgroundColor: '#f4f4f4' }
				}
			>
				<div
					className='dws-title'
					style={done ? { backgroundColor: '#5dc322', color: 'white' } : {}}
				>
					{index}. kép {done ? ' Feltöltve!' : 'feltöltése'}
				</div>
				<div className='dws-content-active'>
					<div style={{ position: 'relative', width: '100%' }}>
						{/* {image && image.src && (
							<div
								id='svg-overlay'
								style={{
									pointerEvents: 'none',
									position: 'absolute',
									zIndex: 9999,
									width: coordinates.w * (400 / imageSize.width),
									height: coordinates.h * (400 / imageSize.width),
									top: coordinates.t * (400 / imageSize.width),
									left: coordinates.l * (400 / imageSize.width),
									background: '#000000f0',
									...clipPathStyle,
								}}
							>
								<img style={{ width: '100%' }} src={''}></img>
							</div>
						)} */}

						{!done && (
							<Cropper
								ref={cropperRef}
								stencilProps={{
									aspectRatio: aspectRatio,
								}}
								defaultSize={defaultSize}
								src={image && image.src}
								onChange={onChange}
								className={'cropper'}
								style={{ marginBottom: 10, width: '100%' }}
							/>
						)}

						<input
							ref={inputRef}
							style={{ display: 'none' }}
							type='file'
							accept='image/*'
							onChange={onLoadImage}
						/>

						<button
							style={{ width: '100%' }}
							className='dws-input'
							onClick={(e) => {
								e.preventDefault()
								inputRef.current.click()
								setDone(false)
							}}
						>
							{image && image.src ? 'Kép cseréje' : 'Kép választása'}
						</button>

						{image && image.src && !done && (
							<button
								style={
									done
										? { backgroundColor: '#5dc322', width: '100%', marginTop: '10px' }
										: { width: '100%', marginTop: '10px' }
								}
								className='dws-input'
								onClick={preview}
							>
								Kész
							</button>
						)}
						{image && image.src && done && (
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
