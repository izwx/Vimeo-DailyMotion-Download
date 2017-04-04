package biz.izwx.stargrabber.server;

import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.util.EntityUtils;
import org.json.JSONObject;

/**
 * Created by deadbeef65 on 2017/02/15.
 */

public class DailyMotionLink {

    public String getVideoLink(String url) {
        StringBuilder sb = new StringBuilder(url);
        sb.insert(27, "json/");
        sb.append("?fields=title,stream_h264_url,stream_h264_ld_url,stream_h264_hq_url,stream_h264_hd_url,stream_h264_hd1080_url");
        String jsonUrl = new String(sb);

        String videoList = getRemoteContent(jsonUrl);
        if((videoList == null)||(videoList.isEmpty())) {
            return null;
        }

        JSONObject info;
        String videoURL;
        try{
            info = new JSONObject(videoList);
            videoURL = info.getString("stream_h264_hq_url");
            if((videoURL == null)||(videoURL.isEmpty())||(videoURL.equals("null"))) {
                videoURL = info.getString("stream_h264_ld_url");
            }
            if((videoURL == null)||(videoURL.isEmpty())||(videoURL.equals("null"))) {
                videoURL = info.getString("stream_h264_url");
            }
            if((videoURL == null)||(videoURL.isEmpty())||(videoURL.equals("null"))) {
                videoURL = info.getString("stream_h264_hd1080_url");
            }
            if((videoURL == null)||(videoURL.isEmpty())||(videoURL.equals("null"))) {
                videoURL = info.getString("stream_h264_hd_url");
            }
            if((videoURL == null)||(videoURL.isEmpty())||(videoURL.equals("null"))) {
                videoURL = null;
            }
        }
        catch (Exception e) {
            return null;
        }

        return videoURL;
    }

    private String getRemoteContent(String url) {
        String content = null;

        try {

            HttpClient httpClient = new DefaultHttpClient();
            HttpGet httpGet = new HttpGet(url);
            HttpResponse httpResponse = httpClient.execute(httpGet);
            content = EntityUtils.toString(httpResponse.getEntity(), "UTF-8");

        } catch (Exception e) {
            content = null;
        }

        return content;
    }
}
