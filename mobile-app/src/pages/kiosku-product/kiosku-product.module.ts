import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { KioskuProductPage } from './kiosku-product';

@NgModule({
  declarations: [
    KioskuProductPage,
  ],
  imports: [
    IonicPageModule.forChild(KioskuProductPage),
    TranslateModule
  ],
})
export class KioskuProductPageModule {}
