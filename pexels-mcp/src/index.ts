#!/usr/bin/env node

import { McpServer } from "@modelcontextprotocol/sdk/server/mcp.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { z } from "zod";

/**
 * Pexels MCP Server
 *
 * MCP server untuk mencari gambar dan video pendek dari Pexels API.
 * Dapat di-install di sistem manapun (Node.js >= 18), termasuk Trae IDE.
 *
 * API Key dibaca dari environment variable PEXELS_API_KEY.
 */

const API_KEY = process.env.PEXELS_API_KEY || "";
const BASE_URL = "https://api.pexels.com";

if (!API_KEY) {
  console.error(
    "PEXELS_API_KEY tidak ditemukan. Set environment variable PEXELS_API_KEY sebelum menjalankan server."
  );
  process.exit(1);
}

/**
 * Helper untuk melakukan request ke Pexels API.
 */
async function pexelsFetch(path: string): Promise<any> {
  const response = await fetch(`${BASE_URL}${path}`, {
    headers: {
      Authorization: API_KEY,
    },
  });

  if (!response.ok) {
    const body = await response.text();
    throw new Error(
      `Pexels API error (${response.status}): ${body || response.statusText}`
    );
  }

  return response.json();
}

/**
 * Normalisasi hasil pencarian foto menjadi struktur ringkas.
 */
function mapPhoto(photo: any) {
  return {
    id: photo.id,
    width: photo.width,
    height: photo.height,
    url: photo.url,
    photographer: photo.photographer,
    photographer_url: photo.photographer_url,
    alt: photo.alt,
    src: {
      original: photo.src?.original,
      large2x: photo.src?.large2x,
      large: photo.src?.large,
      medium: photo.src?.medium,
      small: photo.src?.small,
      portrait: photo.src?.portrait,
      landscape: photo.src?.landscape,
      tiny: photo.src?.tiny,
    },
  };
}

/**
 * Normalisasi hasil search video menjadi struktur ringkas.
 */
function mapVideo(video: any) {
  return {
    id: video.id,
    width: video.width,
    height: video.height,
    url: video.url,
    image: video.image,
    duration: video.duration,
    user: video.user
      ? {
          id: video.user.id,
          name: video.user.name,
          url: video.user.url,
        }
      : null,
    video_files: (video.video_files || []).map((f: any) => ({
      id: f.id,
      quality: f.quality,
      file_type: f.file_type,
      width: f.width,
      height: f.height,
      link: f.link,
    })),
    video_pictures: (video.video_pictures || []).map((p: any) => ({
      id: p.id,
      picture: p.picture,
      nr: p.nr,
    })),
  };
}

const server = new McpServer({
  name: "pexels-mcp",
  version: "1.0.0",
});

// ===== Tool: search_photos =====
server.registerTool(
  "search_photos",
  {
    title: "Cari Foto di Pexels",
    description:
      "Mencari foto/gambar di Pexels berdasarkan kata kunci. Mengembalikan daftar foto dengan URL berbagai ukuran (original, large, medium, small, portrait, landscape).",
    inputSchema: {
      query: z
        .string()
        .describe("Kata kunci pencarian foto, misalnya 'nature', 'office', 'coffee'"),
      per_page: z
        .number()
        .int()
        .min(1)
        .max(80)
        .default(15)
        .describe("Jumlah hasil per halaman (1-80, default 15)"),
      page: z
        .number()
        .int()
        .min(1)
        .default(1)
        .describe("Nomor halaman (default 1)"),
      orientation: z
        .enum(["landscape", "portrait", "square"])
        .optional()
        .describe("Orientasi foto: landscape, portrait, atau square"),
      size: z
        .enum(["large", "medium", "small"])
        .optional()
        .describe("Ukuran foto: large, medium, atau small"),
      color: z
        .string()
        .optional()
        .describe("Filter warna (hex tanpa #, misalnya 'red', '800080', atau 'FF0000')"),
      locale: z
        .string()
        .optional()
        .describe("Kode bahasa hasil, misalnya 'en-US', 'id-ID'"),
    },
  },
  async ({ query, per_page, page, orientation, size, color, locale }) => {
    const params = new URLSearchParams({
      query,
      per_page: String(per_page),
      page: String(page),
    });
    if (orientation) params.set("orientation", orientation);
    if (size) params.set("size", size);
    if (color) params.set("color", color);
    if (locale) params.set("locale", locale);

    const data = await pexelsFetch(`/v1/search?${params.toString()}`);

    return {
      content: [
        {
          type: "text",
          text: JSON.stringify(
            {
              total_results: data.total_results,
              page: data.page,
              per_page: data.per_page,
              photos: (data.photos || []).map(mapPhoto),
            },
            null,
            2
          ),
        },
      ],
    };
  }
);

// ===== Tool: get_photo =====
server.registerTool(
  "get_photo",
  {
    description:
      "Mengambil detail satu foto di Pexels berdasarkan ID foto.",
    inputSchema: {
      id: z.number().int().positive().describe("ID foto Pexels"),
    },
  },
  async ({ id }) => {
    const data = await pexelsFetch(`/v1/photos/${id}`);
    return {
      content: [
        {
          type: "text",
          text: JSON.stringify(mapPhoto(data), null, 2),
        },
      ],
    };
  }
);

// ===== Tool: search_videos =====
server.registerTool(
  "search_videos",
  {
    description:
      "Mencari video pendek di Pexels berdasarkan kata kunci. Mengembalikan URL video (berbagai kualitas) dan thumbnail.",
    inputSchema: {
      query: z
        .string()
        .describe("Kata kunci pencarian video, misalnya 'nature', 'city', 'food'"),
      per_page: z
        .number()
        .int()
        .min(1)
        .max(80)
        .default(15)
        .describe("Jumlah hasil per halaman (1-80, default 15)"),
      page: z
        .number()
        .int()
        .min(1)
        .default(1)
        .describe("Nomor halaman (default 1)"),
      orientation: z
        .enum(["landscape", "portrait", "square"])
        .optional()
        .describe("Orientasi video: landscape, portrait, atau square"),
      size: z
        .enum(["large", "medium", "small"])
        .optional()
        .describe("Ukuran video: large, medium, atau small"),
      locale: z
        .string()
        .optional()
        .describe("Kode bahasa (misalnya 'en-US', 'id-ID')"),
    },
  },
  async ({ query, per_page, page, orientation, size, locale }) => {
    const params = new URLSearchParams({
      query,
      per_page: String(per_page),
      page: String(page),
    });
    if (orientation) params.set("orientation", orientation);
    if (size) params.set("size", size);
    if (locale) params.set("locale", locale);

    const data = await pexelsFetch(`/v1/videos/search?${params.toString()}`);

    return {
      content: [
        {
          type: "text",
          text: JSON.stringify(
            {
              total_results: data.total_results,
              page: data.page,
              per_page: data.per_page,
              videos: (data.videos || []).map(mapVideo),
            },
            null,
            2
          ),
        },
      ],
    };
  }
);

// ===== Tool: get_video =====
server.registerTool(
  "get_video",
  {
    description:
      "Mengambil detail video di Pexels berdasarkan ID.",
    inputSchema: {
      id: z.number().int().positive().describe("ID video Pexels"),
    },
  },
  async ({ id }) => {
    const data = await pexelsFetch(`/v1/videos/${id}`);
    return {
      content: [
        {
          type: "text",
          text: JSON.stringify(mapVideo(data), null, 2),
        },
      ],
    };
  }
);

// ===== Tool: get_curated_photos =====
server.registerTool(
  "get_curated_photos",
  {
    description:
      "Mengambil daftar foto pilihan (curated) dari Pexels, berguna untuk eksplorasi tanpa kata kunci.",
    inputSchema: {
      per_page: z
        .number()
        .int()
        .min(1)
        .max(80)
        .default(15)
        .describe("Jumlah hasil per halaman (1-80, default 15)"),
      page: z
        .number()
        .int()
        .min(1)
        .default(1)
        .describe("Nomor halaman (default 1)"),
    },
  },
  async ({ per_page, page }) => {
    const params = new URLSearchParams({
      per_page: String(per_page),
      page: String(page),
    });
    const data = await pexelsFetch(`/v1/curated?${params.toString()}`);
    return {
      content: [
        {
          type: "text",
          text: JSON.stringify(
            {
              page: data.page,
              per_page: data.per_page,
              photos: (data.photos || []).map(mapPhoto),
            },
            null,
            2
          ),
        },
      ],
    };
  }
);

// ===== Tool: get_popular_videos =====
server.registerTool(
  "get_popular_videos",
  {
    description:
      "Mengambil daftar video populer dari Pexels, tanpa kata kunci.",
    inputSchema: {
      per_page: z
        .number()
        .int()
        .min(1)
        .max(80)
        .default(15)
        .describe("Jumlah hasil per halaman (1-80, default 15)"),
      page: z
        .number()
        .int()
        .min(1)
        .default(1)
        .describe("Nomor halaman (default 1)"),
      min_width: z
        .number()
        .int()
        .positive()
        .optional()
        .describe("Lebar minimum video"),
      min_height: z
        .number()
        .int()
        .positive()
        .optional()
        .describe("Tinggi minimum video"),
      min_duration: z
        .number()
        .int()
        .positive()
        .optional()
        .describe("Durasi minimum video (detik)"),
      max_duration: z
        .number()
        .int()
        .positive()
        .optional()
        .describe("Durasi maksimum video (detik)"),
    },
  },
  async ({ per_page, page, min_width, min_height, min_duration, max_duration }) => {
    const params = new URLSearchParams({
      per_page: String(per_page),
      page: String(page),
    });
    if (min_width) params.set("min_width", String(min_width));
    if (min_height) params.set("min_height", String(min_height));
    if (min_duration) params.set("min_duration", String(min_duration));
    if (max_duration) params.set("max_duration", String(max_duration));

    const data = await pexelsFetch(`/v1/videos/popular?${params.toString()}`);

    return {
      content: [
        {
          type: "text",
          text: JSON.stringify(
            {
              page: data.page,
              per_page: data.per_page,
              videos: (data.videos || []).map(mapVideo),
            },
            null,
            2
          ),
        },
      ],
    };
  }
);

// ===== Jalankan server via stdio =====
const transport = new StdioServerTransport();
await server.connect(transport);
