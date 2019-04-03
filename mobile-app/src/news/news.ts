import { Component } from '@angular/core';
import { IonicPage, NavController, LoadingController } from 'ionic-angular';
import { Bumdesnews } from '../../providers/providers';
import { LoadurlPage, HomePage } from '../../pages/pages';

@IonicPage()
@Component({
  selector: 'page-news',
  templateUrl: 'news.html'
})
export class NewsPage 
{
  cardItems: any[];
  typeSegment: string = 'bumdes';

  constructor(
    public navCtrl: NavController,
    public loading: LoadingController, 
    public news: Bumdesnews) 
  {
    this.fetchNews('bumdes');
  }

	swipe(event) 
	{
    if(this.typeSegment === 'bumdes') 
    {
      if(event.direction === 2) 
      {
        this.typeSegment = 'ukm';
        this.fetchNews('produk+ukm+indonesia');
      }
    }
    else if(this.typeSegment === 'ukm') 
    {
      if(event.direction === 2) 
      {
        this.typeSegment = 'marketplace';
        this.fetchNews('toko+online+indonesia');
      }
      if(event.direction === 4) 
      {
        this.typeSegment = 'bumdes';
        this.fetchNews('bumdes');
      }
    } 
    else 
    {
      if(event.direction === 4) 
      {
        this.typeSegment = 'ukm';
        this.fetchNews('produk+ukm+indonesia');
      }
    }
	}

  fetchNews(param: string)
  {
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
     
    loader.present();

    this.news.get(param).subscribe((res: any) => {
      this.cardItems = res.articles;
      loader.dismiss();
    },
    (err) => {
      this.cardItems = null;
      loader.dismiss();
    })
  }

  goDashboard()
  {
    this.navCtrl.setRoot(HomePage,{},{animate:true})
  }

  openUrl(url:string, title: string)
  {
    this.navCtrl.push(LoadurlPage, {param:{url:url, title:title}});
  }
}
