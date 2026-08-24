<?php
namespace App\Http\Controllers;
use App\Models\ShortLink; use Endroid\QrCode\Builder\Builder; use Endroid\QrCode\Writer\PngWriter; use Endroid\QrCode\Writer\SvgWriter; use Illuminate\Http\Response;
class QrCodeController extends Controller { public function show(ShortLink $link, string $format='png'): Response { $writer=$format==='svg'?new SvgWriter():new PngWriter(); $result=(new Builder(writer: $writer, data: $link->short_url, size: 800, margin: 16))->build(); return response($result->getString(),200,['Content-Type'=>$result->getMimeType(),'Content-Disposition'=>'attachment; filename="msa-go-'.$link->code.'.'.$format.'"']); } }
