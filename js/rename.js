const fs = require('fs')
const path = require('path')

const buildDir = path.resolve(__dirname, 'build')

const renameFile = (filePath, newFileName) => {
	const oldPath = path.join(buildDir, filePath)
	const newPath = path.join(buildDir, newFileName)

	fs.rename(oldPath, newPath, (err) => {
		if (err) {
			console.error(`Error renaming ${filePath} file:`, err)
		} else {
			console.log(`${filePath} file renamed to ${newFileName}`)
		}
	})
}

const renameFiles = (dirName, regexPattern, newFileName) => {
	const dirPath = path.join(buildDir, dirName)
	const files = fs.readdirSync(dirPath)
	files.forEach((file) => {
		if (regexPattern.test(file)) {
			const filePath = path.join(dirName, file)
			const newFilePath = path.join(dirName, newFileName)
			renameFile(filePath, newFilePath)
		}
	})
}

// Define regex patterns to match the dynamically generated filenames
const cssRegex = /^main\.[a-f0-9]+\.css$/
const cssMapRegex = /^main\.[a-f0-9]+\.css.map$/
const jsRegex = /^main\.[a-f0-9]+\.js$/
const jsMapRegex = /^main\.[a-f0-9]+\.js.map$/

// Rename files based on their respective regex patterns
renameFiles('static/css', cssRegex, 'styles.css')
renameFiles('static/css', cssMapRegex, 'styles.css.map')
renameFiles('static/js', jsRegex, 'bundle.js')
renameFiles('static/js', jsMapRegex, 'bundle.js.map')
