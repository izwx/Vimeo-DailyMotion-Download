package biz.izwx.stargrabber.server;

import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.impl.entity.StrictContentLengthStrategy;
import org.apache.http.util.EntityUtils;
import org.json.JSONArray;
import org.json.JSONObject;

/**
 * Created by Shin Izawa on 15/08/28.
 *
 * https://github.com/Kovaloff/Vimeo-Download-Link/blob/master/Vimeo.php
 *
 */
public class VimeoLink {


    public String getVideoLink(String url) {
        String videoUrl = null;

        try {
            JSONObject info = getVimeoVideoInfo(url);
            if (info != null) {

                JSONArray mobile = info.getJSONObject("request").getJSONObject("files").getJSONArray("progressive");
                JSONObject progressive = mobile.getJSONObject(1); // 前は0);
                videoUrl = progressive.getString("url");

            }
        } catch(Exception e) {
            videoUrl = null;
        }

        return videoUrl;
    }

    private JSONObject getVimeoVideoInfo(String url) {
        JSONObject info = null;

        try {

            String pageContent = getRemoteContent(url);

            //vimeo.clip_page_config = から　window.can_preload　まで
            String divName = "clip_page_config =";
            int start = pageContent.indexOf(divName);
            start += divName.length() + 1;
            int end = pageContent.indexOf("};",start);
            end++;
            JSONObject config = new JSONObject(pageContent.substring(start,end));

            //その中のplayer->config_urlを取得
            if(config == null) return null;
            String cofig_url = config.getJSONObject("player").getString("config_url");
            if(cofig_url == null) return null;
            String configResponce = getRemoteContent(cofig_url);

            //String infoResponce = getRemoteContent(infoUrl.replace("&amp;","&"));
            info = new JSONObject(configResponce);


        } catch(Exception e) {
            info = null;
        }

        return info;
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
