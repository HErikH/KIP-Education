import fs from "fs";
import path from "path";
import { pdf } from "pdf-to-img";

export async function convertPdfToImages(pdfPath, outputDir) {
  if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
  }

  const document = await pdf(pdfPath, { scale: 2 });
  const images = [];

  let pageNum = 1;
  for await (const image of document) {
    const imgName = `page_${pageNum}.png`;
    const imgPath = path.join(outputDir, imgName);

    fs.writeFileSync(imgPath, image);
    images.push(imgPath);
    pageNum++;
  }

  return images;
}
