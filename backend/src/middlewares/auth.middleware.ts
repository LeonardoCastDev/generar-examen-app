import { Request, Response, NextFunction } from "express";
import jwt from "jsonwebtoken";
import dotenv from "dotenv";
dotenv.config();

const JWT_SECRET = process.env.JWT_SECRET || "secretkey";

export interface AuthRequest extends Request {
  userId?: number;
}

export function requireAuth(
  req: AuthRequest,
  res: Response,
  next: NextFunction
) {
  const authHeader = req.headers.authorization;
  if (!authHeader)
    return res.status(401).json({ message: "No token provided" });

  const parts = authHeader.split(" ");
  if (parts.length !== 2)
    return res.status(401).json({ message: "Token invalid" });

  const token = parts[1];
  try {
    const payload = jwt.verify(token, JWT_SECRET) as any;
    req.userId = payload.id;
    next();
  } catch (err) {
    return res.status(401).json({ message: "Token invalid" });
  }
}
