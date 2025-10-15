import { Router } from 'express';
import { WhiteboardFilesController } from '../controllers/whiteboardController.js';
import { upload } from '../middlewares/multerConfig.js';

const whiteboardRouter = Router();

whiteboardRouter.post('/:roomId/upload', upload.single('file'), WhiteboardFilesController.uploadFile);
whiteboardRouter.get('/:roomId/files', WhiteboardFilesController.getFiles);
whiteboardRouter.delete('/files/:fileId', WhiteboardFilesController.deleteFile);

export { whiteboardRouter };