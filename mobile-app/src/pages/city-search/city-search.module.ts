import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { CitySearchPage } from './city-search';

@NgModule({
  declarations: [
    CitySearchPage,
  ],
  imports: [
    IonicPageModule.forChild(CitySearchPage),
    TranslateModule
  ],
})
export class CitySearchPageModule {}
