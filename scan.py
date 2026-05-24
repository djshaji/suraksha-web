'''
Simple python tesseract OCR script to
scan video from webcam and extract text from it.
show video feed in a window and print extracted text to console.
'''
import cv2
import pytesseract


def preprocess_for_ocr(frame):
	"""Convert frame to grayscale for OCR without thresholding."""
	gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
	return gray


def main():
	cap = cv2.VideoCapture(0)
	if not cap.isOpened():
		print("Error: Unable to access webcam.")
		return

	print("Press 'q' to quit.")
	frame_index = 0
	ocr_every_n_frames = 8
	last_printed_text = ""

	while True:
		ok, frame = cap.read()
		if not ok:
			print("Warning: Failed to read frame from webcam.")
			break

		display = frame.copy()
		h, w = frame.shape[:2]

		# Lower-middle region usually contains printed roll number text.
		x1 = int(w * 0.10)
		y1 = int(h * 0.55)
		x2 = int(w * 0.90)
		y2 = int(h * 0.92)
		roi = frame[y1:y2, x1:x2]

		cv2.rectangle(display, (x1, y1), (x2, y2), (0, 255, 0), 2)
		cv2.putText(display, "OCR ROI", (x1, max(30, y1 - 10)), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)

		frame_index += 1
		if frame_index % ocr_every_n_frames == 0:
			processed = preprocess_for_ocr(roi)
			text = pytesseract.image_to_string(processed, config="--oem 3 --psm 6").strip()

			if text and text != last_printed_text:
				print("OCR:", text)
				last_printed_text = text

			cv2.imshow("OCR Processed", processed)

		cv2.imshow("Webcam OCR", display)

		if cv2.waitKey(1) & 0xFF == ord('q'):
			break

	cap.release()
	cv2.destroyAllWindows()


if __name__ == "__main__":
	main()